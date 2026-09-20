<?php

use App\Models\Project;
use App\Models\User;
use App\Support\ChecksCatalog;

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
                'hooks' => [
                    'pre-commit' => [['id', 'run']],
                    'commit-msg',
                ],
            ]);
    });

    it('always includes both hook keys, even when one is empty', function () {
        [, $token] = Project::createWithToken(User::factory()->create(), 'Acme');

        // Дефолты — только pre-commit чеки, commitlint не включён по умолчанию.
        withToken($token)
            ->getJson('/api/v1/manifest')
            ->assertJsonPath('hooks.commit-msg', []);
    });

    it('routes a commit-msg check into hooks.commit-msg, not pre-commit', function () {
        [$project, $token] = Project::createWithToken(User::factory()->create(), 'Acme');
        $project->rules()->create(['check_id' => 'commitlint']);

        withToken($token)
            ->getJson('/api/v1/manifest')
            ->assertJsonPath('hooks.commit-msg.0.id', 'commitlint')
            ->assertJsonPath('hooks.commit-msg.0.run', 'npx --no-install commitlint --edit "$1"')
            ->assertJsonCount(4, 'hooks.pre-commit');
    });

    it('returns the default checks for a freshly created project', function () {
        [, $token] = Project::createWithToken(User::factory()->create(), 'Acme');

        $ids = withToken($token)
            ->getJson('/api/v1/manifest')
            ->json('hooks.pre-commit.*.id');

        expect($ids)->toBe(ChecksCatalog::defaults());
    });

    it('reflects only the checks enabled for this project', function () {
        [$project, $token] = Project::createWithToken(User::factory()->create(), 'Acme');
        $project->rules()->delete();
        $project->rules()->create(['check_id' => 'eslint']);
        $project->rules()->create(['check_id' => 'prettier']);

        withToken($token)
            ->getJson('/api/v1/manifest')
            ->assertJsonPath('hooks.pre-commit.0.id', 'eslint')
            ->assertJsonPath('hooks.pre-commit.1.id', 'prettier')
            ->assertJsonCount(2, 'hooks.pre-commit');
    });

    it('orders checks by catalog order, not insertion order', function () {
        [$project, $token] = Project::createWithToken(User::factory()->create(), 'Acme');
        $project->rules()->delete();
        // Вставляем в обратном порядке относительно каталога — манифест
        // всё равно должен вернуть каталожный порядок.
        $project->rules()->create(['check_id' => 'oxfmt']);
        $project->rules()->create(['check_id' => 'pint']);

        $ids = withToken($token)
            ->getJson('/api/v1/manifest')
            ->json('hooks.pre-commit.*.id');

        expect($ids)->toBe(['pint', 'oxfmt']);
    });

    it('returns an empty pre-commit list when no checks are enabled', function () {
        [$project, $token] = Project::createWithToken(User::factory()->create(), 'Acme');
        $project->rules()->delete();

        withToken($token)
            ->getJson('/api/v1/manifest')
            ->assertJsonPath('hooks.pre-commit', []);
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
