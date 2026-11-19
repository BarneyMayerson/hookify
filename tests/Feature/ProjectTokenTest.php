<?php

use App\Models\Project;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\withToken;

// The manifest URL and the Bearer scheme below are the ones the CLI uses;
// adjust them if the API authenticates differently.
const MANIFEST_URL = '/api/v1/manifest';

describe('Project token regeneration', function () {
    it('requires authentication', function () {
        $owner = User::factory()->create();

        [$project] = Project::createWithToken($owner, 'Acme');

        post(route('projects.token.regenerate', $project))->assertRedirect();
    });

    it('flashes a new token exactly once and changes the stored prefix', function () {
        $user = User::factory()->create();

        [$project, $oldToken] = Project::createWithToken($user, 'Acme');
        $oldPrefix = $project->api_token_prefix;

        actingAs($user)
            ->post(route('projects.token.regenerate', $project))
            ->assertRedirect()
            ->assertSessionHas('plaintext_token');

        $newToken = session('plaintext_token');

        expect($newToken)->toBeString()->not->toBe($oldToken)
            ->and($project->fresh()->api_token_prefix)->not->toBe($oldPrefix);
    });

    it('revokes the old token and accepts the new one', function () {
        $user = User::factory()->create();

        [$project, $oldToken] = Project::createWithToken($user, 'Acme');

        actingAs($user)->post(route('projects.token.regenerate', $project));
        $newToken = session('plaintext_token');

        withToken($oldToken)->getJson(MANIFEST_URL)->assertUnauthorized();
        withToken($newToken)->getJson(MANIFEST_URL)->assertOk();
    });

    it('does not let another user regenerate the token', function () {
        $owner = User::factory()->create();

        [$project, $token] = Project::createWithToken($owner, 'Acme');

        actingAs(User::factory()->create())
            ->post(route('projects.token.regenerate', $project))
            ->assertNotFound();

        withToken($token)->getJson(MANIFEST_URL)->assertOk();
    });
});
