<?php

use App\Enums\TokenRejection;
use App\Models\Project;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withToken;

// The manifest URL and the Bearer scheme are the ones the CLI uses; adjust
// them if the API authenticates differently.
describe('Token rejection reasons', function () {
    it('reports a replaced token as revoked', function () {
        $user = User::factory()->create();
        [$project, $oldToken] = Project::createWithToken($user, 'Acme');

        $project->regenerateToken();

        withToken($oldToken)->getJson('/api/v1/manifest')
            ->assertUnauthorized()
            ->assertJsonPath('reason', 'token_revoked');
    });

    it('reports the token of a deleted project as deleted', function () {
        $user = User::factory()->create();
        [$project, $token] = Project::createWithToken($user, 'Acme');

        actingAs($user)->delete(route('projects.destroy', $project))->assertRedirect();

        withToken($token)->getJson('/api/v1/manifest')
            ->assertUnauthorized()
            ->assertJsonPath('reason', 'project_deleted');
    });

    it('reports a token replaced earlier as deleted once the project is gone', function () {
        $user = User::factory()->create();
        [$project, $oldToken] = Project::createWithToken($user, 'Acme');

        $project->regenerateToken();
        actingAs($user)->delete(route('projects.destroy', $project));

        withToken($oldToken)->getJson('/api/v1/manifest')
            ->assertUnauthorized()
            ->assertJsonPath('reason', 'project_deleted');
    });

    it('reports a token that was never issued as invalid', function () {
        withToken('hk_live_'.str_repeat('x', 40))->getJson('/api/v1/manifest')
            ->assertUnauthorized()
            ->assertJsonPath('reason', 'invalid_token');
    });

    it('keeps a regenerated project reachable with its new token', function () {
        $user = User::factory()->create();
        [$project] = Project::createWithToken($user, 'Acme');

        $newToken = $project->regenerateToken();

        withToken($newToken)->getJson('/api/v1/manifest')->assertOk();
    });

    it('tells the reasons apart at the model level', function () {
        $user = User::factory()->create();
        [$project, $first] = Project::createWithToken($user, 'Acme');
        $second = $project->regenerateToken();

        expect(Project::rejectionReason($first))->toBe(TokenRejection::TokenRevoked)
            ->and(Project::rejectionReason('unknown'))->toBe(TokenRejection::InvalidToken);

        $project->delete();

        expect(Project::rejectionReason($first))->toBe(TokenRejection::ProjectDeleted)
            ->and(Project::rejectionReason($second))->toBe(TokenRejection::ProjectDeleted);
    });

    it('reports the tokens of a deleted account as belonging to deleted projects', function () {
        $user = User::factory()->create();
        [, $token] = Project::createWithToken($user, 'Acme');

        $user->delete();

        withToken($token)->getJson('/api/v1/manifest')
            ->assertUnauthorized()
            ->assertJsonPath('reason', 'project_deleted');
    });
});
