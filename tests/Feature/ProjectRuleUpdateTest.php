<?php

use App\Models\Project;
use App\Models\User;
use App\Support\ChecksCatalog;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\put;
use function Pest\Laravel\withToken;

describe('Project rules constructor', function () {
    it('requires authentication', function () {
        [$project] = Project::createWithToken(User::factory()->create(), 'Acme');

        put(route('projects.rules.update', $project), ['check_ids' => ['pint']])
            ->assertRedirect(route('login'));
    });

    it("returns 404 for another user's project", function () {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        [$project] = Project::createWithToken($owner, 'Acme');

        actingAs($stranger)
            ->put(route('projects.rules.update', $project), ['check_ids' => ['pint']])
            ->assertNotFound();
    });

    it('replaces the enabled checks with the submitted set', function () {
        $user = User::factory()->create();
        [$project] = Project::createWithToken($user, 'Acme');

        actingAs($user)
            ->put(route('projects.rules.update', $project), [
                'check_ids' => ['eslint', 'prettier'],
            ])
            ->assertRedirect(route('projects.show', $project));

        expect($project->rules()->pluck('check_id')->all())->toBe(['eslint', 'prettier']);
    });

    it('allows disabling every check', function () {
        $user = User::factory()->create();
        [$project] = Project::createWithToken($user, 'Acme');

        actingAs($user)->put(route('projects.rules.update', $project), ['check_ids' => []]);

        expect($project->rules()->count())->toBe(0);
    });

    it('rejects an unknown check id', function () {
        $user = User::factory()->create();
        [$project] = Project::createWithToken($user, 'Acme');

        actingAs($user)
            ->put(route('projects.rules.update', $project), ['check_ids' => ['not-a-real-check']])
            ->assertSessionHasErrors('check_ids.0');
    });

    it('deduplicates repeated ids', function () {
        $user = User::factory()->create();
        [$project] = Project::createWithToken($user, 'Acme');

        actingAs($user)->put(route('projects.rules.update', $project), [
            'check_ids' => ['pint', 'pint'],
        ]);

        expect($project->rules()->count())->toBe(1);
    });

    it('is reflected by the next manifest fetch', function () {
        [$project, $token] = Project::createWithToken(User::factory()->create(), 'Acme');

        actingAs($project->user)->put(route('projects.rules.update', $project), [
            'check_ids' => ['eslint', 'prettier'],
        ]);

        $ids = withToken($token)->getJson('/api/v1/manifest')->json('hooks.pre-commit.*.id');

        expect($ids)->toBe(['eslint', 'prettier']);
    });
});

describe('Project show page', function () {
    it("returns 404 for another user's project", function () {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        [$project] = Project::createWithToken($owner, 'Acme');

        actingAs($stranger)->get(route('projects.show', $project))->assertNotFound();
    });

    it('exposes the full catalog and the currently enabled ids', function () {
        $user = User::factory()->create();
        [$project] = Project::createWithToken($user, 'Acme');

        $response = actingAs($user)->get(route('projects.show', $project));

        $response->assertInertia(
            fn ($page) => $page
                ->component('Projects/Show')
                ->has('catalog', count(ChecksCatalog::all()))
                ->where('enabledIds', ChecksCatalog::defaults()),
        );
    });
});
