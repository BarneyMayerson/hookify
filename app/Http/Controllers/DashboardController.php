<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectRule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $projects = Project::query()
            ->where('user_id', $user->id)
            ->withCount('rules')
            ->latest('updated_at')
            ->get();

        $activeRulesCount = ProjectRule::query()
            ->whereHas('project', fn ($q) => $q->where('user_id', $user->id))
            ->count();

        $lastSyncedProject = $projects
            ->whereNotNull('last_synced_at')
            ->sortByDesc('last_synced_at')
            ->first();

        $lastSyncTime = $lastSyncedProject?->last_synced_at
            ? $lastSyncedProject->last_synced_at->diffForHumans()
            : null;

        $recentProjects = $projects->take(5)->map(fn (Project $project) => [
            'id' => $project->id,
            'name' => $project->name,
            'api_token_prefix' => $project->api_token_prefix,
            'active_rules_count' => $project->rules_count,
            'last_synced_at' => $project->last_synced_at?->diffForHumans() ?? null,
        ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalProjects' => $projects->count(),
                'activeRulesCount' => $activeRulesCount,
                'lastSyncTime' => $lastSyncTime,
            ],
            'recentProjects' => $recentProjects,
        ]);
    }
}
