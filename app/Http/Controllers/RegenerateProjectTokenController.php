<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegenerateProjectTokenController extends Controller
{
    public function __invoke(Request $request, Project $project): RedirectResponse
    {
        // Same rule as the rest of the project pages: only the owner, and a
        // foreign project looks like a missing one.
        abort_unless($project->user_id === $request->user()->id, 404);

        // The old token stops working immediately: only the hash of the new
        // one is stored. The plaintext is flashed, so it is shown exactly once.
        $token = $project->regenerateToken();

        return back()->with('plaintext_token', $token);
    }
}
