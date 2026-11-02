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

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer gh_token_123') &&
            $request->hasHeader('User-Agent', 'Hookify-App') &&
            $request->hasHeader('X-GitHub-Api-Version', '2026-03-10')
        );
    });

    it('returns pagination metadata and requests the requested GitHub page', function () {
        $user = User::factory()->create(['github_token' => 'gh_token_123']);

        Http::fake([
            'api.github.com/user/repos*' => Http::response([
                ['id' => 3, 'full_name' => 'acme/three', 'private' => false],
            ], 200, [
                'Link' => '<https://api.github.com/user/repos?page=3&per_page=100>; rel="next", <https://api.github.com/user/repos?page=8&per_page=100>; rel="last"',
            ]),
        ]);

        actingAs($user)
            ->getJson(route('github.repositories', ['page' => 2]))
            ->assertOk()
            ->assertJsonPath('page', 2)
            ->assertJsonPath('has_more', true)
            ->assertJsonPath('repositories.0.id', 3);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'page=2') &&
            str_contains($request->url(), 'per_page=100')
        );
    });

    it('rejects invalid page numbers', function () {
        $user = User::factory()->create(['github_token' => 'gh_token_123']);
        Http::fake();

        actingAs($user)
            ->getJson(route('github.repositories', ['page' => 0]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('page');

        Http::assertNothingSent();
    });

    it('marks the final page when GitHub has no next link', function () {
        $user = User::factory()->create(['github_token' => 'gh_token_123']);

        Http::fake([
            'api.github.com/user/repos*' => Http::response([
                ['id' => 1, 'full_name' => 'acme/one', 'private' => false],
            ], 200, [
                'Link' => '<https://api.github.com/user/repos?page=1&per_page=100>; rel="first"',
            ]),
        ]);

        actingAs($user)
            ->getJson(route('github.repositories'))
            ->assertOk()
            ->assertJsonPath('page', 1)
            ->assertJsonPath('has_more', false);
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
