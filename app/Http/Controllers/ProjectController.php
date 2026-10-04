<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Support\ChecksCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $projects = $request->user()->projects()
            ->with('rules')
            ->latest()
            ->get([
                'id', 'name', 'api_token_prefix', 'github_repo_id',
                'github_repo_full_name', 'last_synced_at',
            ]);

        return Inertia::render('Projects/Index', [
            'projects' => $projects->map(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'api_token_prefix' => $project->api_token_prefix,
                'github_repo_id' => $project->github_repo_id,
                'github_repo_full_name' => $project->github_repo_full_name,
                'last_synced_at' => $project->last_synced_at,
                'enabledIds' => $project->rules->pluck('check_id'),
            ]),
            'catalog' => collect(ChecksCatalog::all())
                ->map(fn (array $check, string $id) => ['id' => $id, 'label' => $check['label']])
                ->values(),
            // Not relying on the CLI's default (HOOKIFY_API is hardcoded to
            // https://hookify.dev/api/v1) — the command must point at
            // whichever server it was copied from (dev .lan, prod, etc.).
            'apiBase' => rtrim(config('app.url'), '/').'/api/v1',
        ]);
    }

    public function show(Request $request, Project $project): Response
    {
        abort_unless($project->user_id === $request->user()->id, 404);

        return Inertia::render('Projects/Show', [
            'project' => $project->only([
                'id', 'name', 'api_token_prefix', 'github_repo_id', 'github_repo_full_name',
            ]),
            'catalog' => collect(ChecksCatalog::all())
                ->map(fn (array $check, string $id) => [
                    'id' => $id,
                    'label' => $check['label'],
                    'ecosystem' => $check['ecosystem'],
                    'tier' => $check['tier'],
                    'requiresConfig' => $check['requiresConfig'],
                    'requiresBinary' => $check['requiresBinary'],
                ])
                ->values(),
            'enabledIds' => $project->rules()->pluck('check_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // No CR/LF/NUL
            'name' => ['required', 'string', 'max:255', 'regex:/^[^\r\n\0]*$/'],
        ]);

        [, $token] = Project::createWithToken($request->user(), $validated['name']);

        // The token goes into flash, not a page prop: it survives exactly
        // one redirect and disappears on refresh/revisit — it can't be
        // fetched from the server again anyway, since only its hash is stored.
        return Redirect::route('projects.index')->with('plaintext_token', $token);
    }
}
