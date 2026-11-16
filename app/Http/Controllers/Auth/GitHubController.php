<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GitHubController extends Controller
{
    /**
     * Marks that the current round trip to GitHub is the "give me access to
     * private repositories" flow, not a regular sign-in.
     */
    private const string REPO_ACCESS_FLOW = 'github_repo_access_flow';

    /**
     * The project whose repository picker was open when the user left for
     * GitHub, so the picker can be reopened on return.
     */
    private const string REPO_ACCESS_PROJECT = 'github_repo_access_project';

    /**
     * Sign-in asks for identity only (Socialite's default scope is
     * user:email). Without extra scopes the GitHub API lists public
     * repositories only; access to private ones is requested separately,
     * when the user asks for it — see requestRepoAccess().
     */
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Sends an already signed-in user back through GitHub with the broader
     * `repo` scope. GitHub OAuth Apps have no read-only scope for private
     * repositories, so this is the narrowest one that lists them.
     */
    public function requestRepoAccess(Request $request): SymfonyRedirectResponse
    {
        $validated = $request->validate([
            'project' => ['sometimes', 'integer'],
        ]);

        // Only a project the user owns can be remembered for the return trip.
        $projectId = isset($validated['project'])
            ? $request->user()?->projects()->whereKey($validated['project'])->value('id')
            : null;

        session([
            self::REPO_ACCESS_FLOW => true,
            self::REPO_ACCESS_PROJECT => $projectId,
        ]);

        /** @var GithubProvider $driver */
        $driver = Socialite::driver('github');

        return $driver->scopes(['repo'])->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        // Read the flag before Auth::login() migrates the session.
        $repoAccessFlow = (bool) session()->pull(self::REPO_ACCESS_FLOW, false);
        $projectId = session()->pull(self::REPO_ACCESS_PROJECT);

        // The user pressed "Cancel" on GitHub's consent screen.
        if ($request->filled('error')) {
            return $repoAccessFlow
                ? redirect()->route('projects.index')
                    ->withErrors(['github' => 'Access to private repositories was not granted.'])
                : redirect()->route('login')
                    ->withErrors(['github' => 'GitHub sign-in was cancelled.']);
        }

        /** @var SocialiteUser $githubUser */
        $githubUser = Socialite::driver('github')->user();

        // Someone signed in as user A must not become user B by authorizing
        // with a different GitHub account during the repo-access flow.
        if ($repoAccessFlow && (string) Auth::user()?->github_id !== (string) $githubUser->getId()) {
            abort(403, 'This GitHub account does not match the signed-in user.');
        }

        $user = User::firstOrNew(['github_id' => $githubUser->getId()]);

        // The profile is not editable in the app: name and email mirror
        // GitHub and are refreshed on every sign-in (see Profile settings).
        $user->fill([
            'name' => $githubUser->getName() ?: $githubUser->getNickname(),
            'email' => $this->resolveEmail($user, $githubUser),
            'github_nickname' => $githubUser->getNickname(),
            'github_avatar' => $githubUser->getAvatar(),
            // Used later to list the user's repos for project binding.
            // `encrypted`-cast and $hidden on User.
            'github_token' => $githubUser->token,
            // What GitHub actually granted: the user can untick scopes on
            // the consent screen, so don't assume what was requested.
            'github_scopes' => $githubUser->approvedScopes,
        ])->save();

        Auth::login($user, remember: true);

        // Back to the projects page; the flash tells the page which
        // repository picker to reopen, now with private repositories listed.
        return $repoAccessFlow
            ? redirect()->route('projects.index')->with('open_repo_picker', $projectId)
            : redirect()->intended(route('dashboard'));
    }

    /**
     * GitHub can hide the email (a privacy setting) — then it's null. Keep
     * the address we already have; a new account gets a unique placeholder
     * instead of failing a NOT NULL/unique constraint.
     *
     * An address that another account already owns (it was released on
     * GitHub and taken by someone else) must not fail the sign-in either.
     */
    private function resolveEmail(User $user, SocialiteUser $githubUser): string
    {
        $fallback = $user->exists
            ? $user->email
            : "{$githubUser->getId()}+github@users.noreply.hookify.to";
        $email = $githubUser->getEmail();

        if ($email === null) {
            return $fallback;
        }

        $takenByAnother = User::where('email', $email)
            ->when($user->exists, fn ($query) => $query->whereKeyNot($user->getKey()))
            ->exists();

        return $takenByAnother ? $fallback : $email;
    }
}
