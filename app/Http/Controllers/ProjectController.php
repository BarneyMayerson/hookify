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
        return Inertia::render('Projects/Index', [
            'projects' => $request->user()->projects()
                ->latest()
                ->get(['id', 'name', 'api_token_prefix', 'last_synced_at']),
            'apiBase' => rtrim(config('app.url'), '/').'/api/v1',
        ]);
    }

    public function show(Request $request, Project $project): Response
    {
        abort_unless($project->user_id === $request->user()->id, 404);

        return Inertia::render('Projects/Show', [
            'project' => $project->only(['id', 'name', 'api_token_prefix']),
            'catalog' => collect(ChecksCatalog::all())
                ->map(fn (array $check, string $id) => [
                    'id' => $id,
                    'label' => $check['label'],
                    'ecosystem' => $check['ecosystem'],
                    'tier' => $check['tier'],
                    'requiresConfig' => $check['requiresConfig'],
                ])
                ->values(),
            'enabledIds' => $project->rules()->pluck('check_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        [, $token] = Project::createWithToken($request->user(), $validated['name']);

        // Токен кладём во flash, а не в проп страницы: переживает только
        // один редирект, при обновлении/повторном заходе на страницу исчезает —
        // повторно достать его с сервера уже нельзя (хранится только хеш).
        return Redirect::route('projects.index')->with('plaintext_token', $token);
    }
}
