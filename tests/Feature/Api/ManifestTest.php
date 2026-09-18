<?php

use App\Models\Project;
use App\Models\User;

use function Pest\Laravel\getJson;
use function Pest\Laravel\withToken;

describe('Manifest API', function () {
    it('rejects a request without a token', function () {
        getJson('/api/v1/manifest')->assertUnauthorized();
    });

    it('rejects a request with an unknown token', function () {
        withToken('hk_live_nonexistent')
            ->getJson('/api/v1/manifest')
            ->assertUnauthorized();
    });

    it('returns the manifest for a valid token', function () {
        [$project, $token] = Project::createWithToken(User::factory()->create(), 'Acme');

        withToken($token)
            ->getJson('/api/v1/manifest')
            ->assertOk()
            ->assertJsonPath('schema_version', 1)
            ->assertJsonPath('project.id', $project->id)
            ->assertJsonPath('project.name', 'Acme')
            ->assertJsonStructure([
                'schema_version',
                'project' => ['id', 'name'],
                'hooks' => ['pre-commit' => [['id', 'run']]],
            ]);
    });

    it('never exposes the token hash', function () {
        [, $token] = Project::createWithToken(User::factory()->create(), 'Acme');

        $response = withToken($token)->getJson('/api/v1/manifest');

        expect($response->json())->not->toHaveKey('api_token_hash');
    });

    it('stores only a hash of the token', function () {
        [$project, $token] = Project::createWithToken(User::factory()->create(), 'Acme');

        expect($project->api_token_hash)->not->toBe($token)
            ->and($project->api_token_hash)->toBe(hash('sha256', $token));
    });

    it('records the sync timestamp', function () {
        [$project, $token] = Project::createWithToken(User::factory()->create(), 'Acme');

        expect($project->last_synced_at)->toBeNull();

        withToken($token)->getJson('/api/v1/manifest')->assertOk();

        expect($project->fresh()->last_synced_at)->not->toBeNull();
    });
});
