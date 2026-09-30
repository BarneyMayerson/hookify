<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GitHubController extends Controller
{
    public function redirect(): SymfonyRedirectResponse
    {
        /** @var GithubProvider $driver */
        $driver = Socialite::driver('github');

        // 'repo' — otherwise the GitHub API only returns public repositories
        // when we later fetch the list for project binding.
        return $driver->scopes(['repo'])->redirect();
    }

    public function callback(): RedirectResponse
    {
        /** @var SocialiteUser $githubUser */
        $githubUser = Socialite::driver('github')->user();

        $user = User::updateOrCreate(
            ['github_id' => $githubUser->getId()],
            [
                'name' => $githubUser->getName() ?: $githubUser->getNickname(),
                // GitHub can hide the email (a privacy setting) — then it's null.
                // A unique fallback instead of failing a NOT NULL/unique constraint.
                'email' => $githubUser->getEmail()
                    ?? "{$githubUser->getId()}+github@users.noreply.hookify.dev",
                'github_nickname' => $githubUser->getNickname(),
                'github_avatar' => $githubUser->getAvatar(),
                // Used later to list the user's repos for project binding.
                // Must be `encrypted`-cast and $hidden on User — see note there.
                'github_token' => $githubUser->token,
            ],
        );

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}
