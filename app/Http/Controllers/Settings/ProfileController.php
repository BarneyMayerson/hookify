<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     *
     * Read-only: name/email/avatar are synced from GitHub on every login
     * (see GitHubController::callback) and are not editable here.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/Profile');
    }

    /**
     * Delete the user's profile.
     *
     * No password confirmation: GitHub OAuth is the only auth method in
     * this app, so there is no credential left to re-check — the `auth`
     * middleware already guarantees only the signed-in user reaches this
     * route. The frontend's "type DELETE to confirm" is a mistake-guard,
     * not a security boundary.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
