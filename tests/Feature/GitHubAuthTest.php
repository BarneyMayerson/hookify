<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\get;

function mockGithubUser(
    string $id,
    ?string $name,
    string $nickname,
    ?string $email,
    ?string $avatar = null,
): void {
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn($id);
    $socialiteUser->shouldReceive('getName')->andReturn($name);
    $socialiteUser->shouldReceive('getNickname')->andReturn($nickname);
    $socialiteUser->shouldReceive('getEmail')->andReturn($email);
    $socialiteUser->shouldReceive('getAvatar')->andReturn($avatar);

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);
}

describe('GitHub OAuth', function () {
    it('redirects to github', function () {
        $response = get(route('auth.github.redirect'));

        $response->assertStatus(302);
        expect($response->headers->get('Location'))->toContain('github.com');
    });

    it('creates a new user on first callback', function () {
        mockGithubUser('12345', 'Ivan Petrov', 'ivanpetrov', 'ivan@example.com');

        get(route('auth.github.callback'))->assertRedirect(route('dashboard'));

        assertAuthenticated();
        expect(User::where('github_id', '12345')->first())
            ->name->toBe('Ivan Petrov')
            ->github_nickname->toBe('ivanpetrov');
    });

    it('falls back to a placeholder email when github hides it', function () {
        mockGithubUser('999', null, 'privateuser', null);

        get(route('auth.github.callback'));

        $user = User::where('github_id', '999')->first();

        expect($user->email)->toBe('999+github@users.noreply.hookify.dev')
            ->and($user->name)->toBe('privateuser');
    });

    it('logs in an existing user on repeat visits without duplicating', function () {
        $existing = User::factory()->create(['github_id' => '555']);

        mockGithubUser('555', 'Updated Name', 'someone', 'someone@example.com');

        get(route('auth.github.callback'));

        assertAuthenticated();
        expect(User::count())->toBe(1)
            ->and($existing->fresh()->name)->toBe('Updated Name');
    });
});
