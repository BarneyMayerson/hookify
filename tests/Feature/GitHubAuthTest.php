<?php

use App\Models\Project;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\get;

/**
 * A real Socialite user instead of a Mockery mock: no stubbed getters to keep
 * in sync with the controller, and PHPStan knows the property types.
 *
 * @param  list<string>  $scopes
 */
function mockGithubUser(
    string $id,
    ?string $name,
    string $nickname,
    ?string $email,
    ?string $avatar = null,
    string $token = 'gh-token',
    array $scopes = ['user:email'],
): void {
    $socialiteUser = (new SocialiteUser)->map([
        'id' => $id,
        'name' => $name,
        'nickname' => $nickname,
        'email' => $email,
        'avatar' => $avatar,
    ]);

    $socialiteUser->token = $token;
    $socialiteUser->approvedScopes = $scopes;

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);
}

/**
 * @return list<string>
 */
function requestedScopes(?string $location): array
{
    parse_str((string) parse_url((string) $location, PHP_URL_QUERY), $query);

    $scope = $query['scope'] ?? '';

    return is_string($scope) ? explode(',', $scope) : [];
}

describe('GitHub OAuth', function () {
    it('redirects to github', function () {
        $response = get(route('auth.github.redirect'));

        $response->assertStatus(302);
        expect($response->headers->get('Location'))->toContain('github.com');
    });

    it('asks only for the identity scope on sign-in', function () {
        $response = get(route('auth.github.redirect'))->assertRedirect();

        expect(requestedScopes($response->headers->get('Location')))->toBe(['user:email']);
    });

    it('creates a new user on first callback', function () {
        mockGithubUser('12345', 'Ivan Petrov', 'ivanpetrov', 'ivan@example.com');

        get(route('auth.github.callback'))->assertRedirect(route('dashboard'));

        assertAuthenticated();
        expect(User::where('github_id', '12345')->first())
            ->name->toBe('Ivan Petrov')
            ->github_nickname->toBe('ivanpetrov');
    });

    it('stores the scopes github actually granted', function () {
        mockGithubUser('12345', 'Ivan Petrov', 'ivanpetrov', 'ivan@example.com', scopes: ['user:email']);

        get(route('auth.github.callback'));

        expect(User::where('github_id', '12345')->first())
            ->github_token->toBe('gh-token')
            ->github_scopes->toBe(['user:email']);
    });

    it('falls back to a placeholder email when github hides it', function () {
        mockGithubUser('999', null, 'privateuser', null);

        get(route('auth.github.callback'));

        $user = User::where('github_id', '999')->first();

        expect($user->email)->toBe('999+github@users.noreply.hookify.to')
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

    it('syncs name and email from github on repeat visits', function () {
        $existing = User::factory()->create([
            'github_id' => '555',
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        mockGithubUser('555', 'New Name', 'someone', 'new@example.com');

        get(route('auth.github.callback'));

        expect($existing->fresh())
            ->name->toBe('New Name')
            ->email->toBe('new@example.com');
    });

    it('keeps the stored email when github hides it', function () {
        $existing = User::factory()->create(['github_id' => '555', 'email' => 'real@example.com']);

        mockGithubUser('555', 'Someone', 'someone', null);

        get(route('auth.github.callback'));

        expect($existing->fresh()->email)->toBe('real@example.com');
    });

    it('keeps the stored email when another account already owns the new one', function () {
        User::factory()->create(['github_id' => '111', 'email' => 'taken@example.com']);
        $existing = User::factory()->create(['github_id' => '555', 'email' => 'mine@example.com']);

        mockGithubUser('555', 'Someone', 'someone', 'taken@example.com');

        get(route('auth.github.callback'));

        assertAuthenticated();
        expect($existing->fresh()->email)->toBe('mine@example.com');
    });

    it('uses a placeholder when a new account clashes with an existing email', function () {
        User::factory()->create(['github_id' => '111', 'email' => 'taken@example.com']);

        mockGithubUser('777', 'Newcomer', 'newcomer', 'taken@example.com');

        get(route('auth.github.callback'));

        assertAuthenticated();
        expect(User::where('github_id', '777')->first()->email)
            ->toBe('777+github@users.noreply.hookify.to');
    });

    it('refreshes the github profile fields and the token on repeat visits', function () {
        User::factory()->create([
            'github_id' => '555',
            'github_nickname' => 'oldnick',
            'github_token' => 'old-token',
            'github_scopes' => ['user:email'],
        ]);

        mockGithubUser(
            '555',
            'Whatever',
            'newnick',
            'someone@example.com',
            'https://avatars.example.com/555.png',
            'new-token',
            ['user:email', 'repo'],
        );

        get(route('auth.github.callback'));

        expect(User::where('github_id', '555')->first())
            ->github_nickname->toBe('newnick')
            ->github_avatar->toBe('https://avatars.example.com/555.png')
            ->github_token->toBe('new-token')
            ->github_scopes->toBe(['user:email', 'repo']);
    });

    it('goes back with an error when the user cancels on github', function () {
        get(route('auth.github.callback', ['error' => 'access_denied']))
            ->assertRedirect()
            ->assertSessionHasErrors('github');
    });
});

describe('GitHub repository access', function () {
    it('requires a signed-in user', function () {
        get(route('auth.github.repo-access'))->assertRedirect();
    });

    it('asks for the repo scope on top of the identity scope', function () {
        $response = actingAs(User::factory()->create())
            ->get(route('auth.github.repo-access'))
            ->assertRedirect();

        expect(requestedScopes($response->headers->get('Location')))->toBe(['user:email', 'repo']);
    });

    it('stores the repo scope and returns to the projects page', function () {
        $user = User::factory()->create(['github_id' => '1', 'github_scopes' => ['user:email']]);

        mockGithubUser('1', 'Ivan', 'ivan', 'ivan@example.com', scopes: ['user:email', 'repo']);

        actingAs($user)
            ->withSession(['github_repo_access_flow' => true])
            ->get(route('auth.github.callback'))
            ->assertRedirect(route('projects.index'));

        expect($user->fresh()->hasRepoAccess())->toBeTrue();
    });

    it('rejects a different github account and keeps the stored token', function () {
        $user = User::factory()->create([
            'github_id' => '1',
            'github_token' => 'original-token',
            'github_scopes' => ['user:email'],
        ]);

        mockGithubUser('2', 'Someone Else', 'someoneelse', 'else@example.com', scopes: ['user:email', 'repo']);

        actingAs($user)
            ->withSession(['github_repo_access_flow' => true])
            ->get(route('auth.github.callback'))
            ->assertForbidden();

        expect($user->fresh())
            ->github_token->toBe('original-token')
            ->github_scopes->toBe(['user:email']);
    });

    it('returns to the projects page with an error when the user cancels', function () {
        actingAs(User::factory()->create())
            ->withSession(['github_repo_access_flow' => true])
            ->get(route('auth.github.callback', ['error' => 'access_denied']))
            ->assertRedirect(route('projects.index'))
            ->assertSessionHasErrors('github');
    });

    it('remembers the project whose picker was open', function () {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();

        actingAs($user)
            ->get(route('auth.github.repo-access', ['project' => $project->id]))
            ->assertRedirect()
            ->assertSessionHas('github_repo_access_project', $project->id);
    });

    it('ignores a project the user does not own', function () {
        $user = User::factory()->create();
        $foreign = Project::factory()->create();

        actingAs($user)
            ->get(route('auth.github.repo-access', ['project' => $foreign->id]))
            ->assertRedirect()
            ->assertSessionMissing('github_repo_access_project');
    });

    it('tells the projects page which picker to reopen', function () {
        $user = User::factory()->create(['github_id' => '1']);
        $project = Project::factory()->for($user)->create();

        mockGithubUser('1', 'Ivan', 'ivan', 'ivan@example.com', scopes: ['user:email', 'repo']);

        actingAs($user)
            ->withSession([
                'github_repo_access_flow' => true,
                'github_repo_access_project' => $project->id,
            ])
            ->get(route('auth.github.callback'))
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('open_repo_picker', $project->id);
    });

    it('passes the picker to reopen once and then forgets it', function () {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();

        actingAs($user)
            ->withSession(['open_repo_picker' => $project->id])
            ->get(route('projects.index'))
            ->assertInertia(fn (Assert $page) => $page->where('openRepoPickerFor', $project->id));

        actingAs($user)
            ->get(route('projects.index'))
            ->assertInertia(fn (Assert $page) => $page->where('openRepoPickerFor', null));
    });
});

describe('User::hasRepoAccess', function () {
    it('is true only when the repo scope was granted', function (?array $scopes, bool $expected) {
        $user = User::factory()->make(['github_scopes' => $scopes]);

        expect($user->hasRepoAccess())->toBe($expected);
    })->with([
        'repo granted' => [['user:email', 'repo'], true],
        'identity only' => [['user:email'], false],
        'unknown scopes' => [null, false],
    ]);
});
