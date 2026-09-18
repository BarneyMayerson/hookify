<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GitHubController extends Controller
{
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $githubUser = Socialite::driver('github')->user();

        $user = User::updateOrCreate(
            ['github_id' => $githubUser->getId()],
            [
                'name' => $githubUser->getName() ?: $githubUser->getNickname(),
                // GitHub может скрыть email (настройка приватности) — тогда null.
                // Уникальный fallback вместо падения на NOT NULL/unique constraint.
                'email' => $githubUser->getEmail()
                    ?? "{$githubUser->getId()}+github@users.noreply.hookify.dev",
                'github_nickname' => $githubUser->getNickname(),
                'github_avatar' => $githubUser->getAvatar(),
            ],
        );

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}
