<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $projects = $request->user()->projects()
            ->withCount('rules')
            ->latest('updated_at')
            ->get();

        $lastSyncedProject = $projects
            ->whereNotNull('last_synced_at')
            ->sortByDesc('last_synced_at')
            ->first();

        $lastSyncTime = $lastSyncedProject?->last_synced_at?->diffForHumans();

        $recentProjects = $projects->take(5)->map(fn (Project $project) => [
            'id' => $project->id,
            'name' => $project->name,
            'api_token_prefix' => $project->api_token_prefix,
            'active_rules_count' => $project->rules_count,
            'last_synced_at' => $project->last_synced_at?->diffForHumans(),
        ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalProjects' => $projects->count(),
                // Already loaded per-project via withCount — no need for
                // a second query against project_rules.
                'activeRulesCount' => $projects->sum('rules_count'),
                'lastSyncTime' => $lastSyncTime,
            ],
            'recentProjects' => $recentProjects,
        ]);
    }
}
