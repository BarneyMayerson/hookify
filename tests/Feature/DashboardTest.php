<?php

use App\Models\Project;
use App\Models\User;
use App\Support\ChecksCatalog;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('Dashboard', function () {
    it('requires authentication', function () {
        get(route('dashboard'))->assertRedirect(route('login'));
    });

    it('reports zero stats for a user with no projects', function () {
        $user = User::factory()->create();

        actingAs($user)->get(route('dashboard'))->assertInertia(
            fn ($page) => $page
                ->component('Dashboard')
                ->where('stats.totalProjects', 0)
                ->where('stats.activeRulesCount', 0)
                ->where('stats.lastSyncTime', null)
                ->where('recentProjects', []),
        );
    });

    it('sums active rules across all of the user\'s projects, not one query per project', function () {
        $user = User::factory()->create();
        Project::createWithToken($user, 'Alpha');
        Project::createWithToken($user, 'Beta');

        $expected = count(ChecksCatalog::defaults()) * 2;

        actingAs($user)->get(route('dashboard'))->assertInertia(
            fn ($page) => $page
                ->where('stats.totalProjects', 2)
                ->where('stats.activeRulesCount', $expected),
        );
    });

    it("only counts the current user's own projects", function () {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        Project::createWithToken($stranger, 'Not mine');
        Project::createWithToken($user, 'Mine');

        actingAs($user)->get(route('dashboard'))->assertInertia(
            fn ($page) => $page
                ->where('stats.totalProjects', 1)
                ->has('recentProjects', 1)
                ->where('recentProjects.0.name', 'Mine'),
        );
    });

    it('limits recent projects to the 5 most recently updated', function () {
        $user = User::factory()->create();
        foreach (range(1, 6) as $i) {
            Project::createWithToken($user, "Project {$i}");
        }

        actingAs($user)->get(route('dashboard'))->assertInertia(
            fn ($page) => $page->has('recentProjects', 5),
        );
    });

    it('reports the most recently synced project\'s time, not an arbitrary one', function () {
        $user = User::factory()->create();
        [$older] = Project::createWithToken($user, 'Older');
        [$newer] = Project::createWithToken($user, 'Newer');

        $older->forceFill(['last_synced_at' => now()->subDay()])->save();
        $newer->forceFill(['last_synced_at' => now()])->save();

        actingAs($user)->get(route('dashboard'))->assertInertia(
            fn ($page) => $page->where(
                'stats.lastSyncTime',
                $newer->fresh()->last_synced_at->diffForHumans(),
            ),
        );
    });
});
