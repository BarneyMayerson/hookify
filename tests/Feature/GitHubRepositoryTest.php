<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('GitHub repository listing', function () {
    it('requires authentication', function () {
        getJson(route('github.repositories'))->assertUnauthorized();
    });

    it('returns 409 when the user has no github token', function () {
        $user = User::factory()->create(['github_token' => null]);

        actingAs($user)
            ->getJson(route('github.repositories'))
            ->assertStatus(409)
            ->assertJsonPath('message', 'No GitHub token on file. Please reconnect your GitHub account.');
    });

    it('returns the mapped repository list on success', function () {
        $user = User::factory()->create(['github_token' => 'gh_token_123']);

        Http::fake([
            'api.github.com/user/repos*' => Http::response([
                ['id' => 1, 'full_name' => 'acme/one', 'private' => false, 'extra' => 'ignored'],
                ['id' => 2, 'full_name' => 'acme/two', 'private' => true, 'extra' => 'ignored'],
            ], 200),
        ]);

        actingAs($user)
            ->getJson(route('github.repositories'))
            ->assertOk()
            ->assertJson([
                'repositories' => [
                    ['id' => 1, 'full_name' => 'acme/one', 'private' => false],
                    ['id' => 2, 'full_name' => 'acme/two', 'private' => true],
                ],
            ]);

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer gh_token_123'));
    });

    it('returns 409 when the github token was revoked', function () {
        $user = User::factory()->create(['github_token' => 'gh_stale_token']);

        Http::fake(['api.github.com/user/repos*' => Http::response(['message' => 'Bad credentials'], 401)]);

        actingAs($user)
            ->getJson(route('github.repositories'))
            ->assertStatus(409)
            ->assertJsonPath('message', 'GitHub token is no longer valid. Please reconnect your GitHub account.');
    });

    it('returns 502 on an unexpected github failure', function () {
        $user = User::factory()->create(['github_token' => 'gh_token_123']);

        Http::fake(['api.github.com/user/repos*' => Http::response('', 500)]);

        actingAs($user)->getJson(route('github.repositories'))->assertStatus(502);
    });
});
