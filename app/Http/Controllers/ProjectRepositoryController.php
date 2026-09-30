<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ProjectRepositoryController extends Controller
{
    public function update(Request $request, Project $project): RedirectResponse
    {
        // 404, not 403 — don't confirm to another user that a project
        // with this id even exists.
        abort_unless($project->user_id === $request->user()->id, 404);

        $validated = $request->validate([
            // Both null together = unlink. Either both present or both
            // absent — a lone id or a lone name is a malformed request.
            'github_repo_id' => ['nullable', 'integer', 'required_with:github_repo_full_name'],
            'github_repo_full_name' => ['nullable', 'string', 'max:255', 'required_with:github_repo_id'],
        ]);

        $project->update([
            'github_repo_id' => $validated['github_repo_id'] ?? null,
            'github_repo_full_name' => $validated['github_repo_full_name'] ?? null,
        ]);

        return Redirect::route('projects.show', $project);
    }
}
