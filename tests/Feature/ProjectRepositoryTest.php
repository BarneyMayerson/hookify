<?php

use App\Models\Project;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

describe('Project repository binding', function () {
    it('requires authentication', function () {
        [$project] = Project::createWithToken(User::factory()->create(), 'Acme');

        patch(route('projects.repository.update', $project), [
            'github_repo_id' => 1,
            'github_repo_full_name' => 'acme/one',
        ])->assertRedirect(route('login'));
    });

    it("returns 404 for another user's project", function () {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        [$project] = Project::createWithToken($owner, 'Acme');

        actingAs($stranger)
            ->patch(route('projects.repository.update', $project), [
                'github_repo_id' => 1,
                'github_repo_full_name' => 'acme/one',
            ])
            ->assertNotFound();
    });

    it('links a repository to the project', function () {
        $user = User::factory()->create();
        [$project] = Project::createWithToken($user, 'Acme');

        actingAs($user)
            ->patch(route('projects.repository.update', $project), [
                'github_repo_id' => 42,
                'github_repo_full_name' => 'acme/hookify',
            ])
            ->assertRedirect(route('projects.show', $project));

        expect($project->fresh())
            ->github_repo_id->toBe(42)
            ->github_repo_full_name->toBe('acme/hookify');
    });

    it('unlinks a repository when both fields are sent as null', function () {
        $user = User::factory()->create();
        [$project] = Project::createWithToken($user, 'Acme');
        $project->update(['github_repo_id' => 42, 'github_repo_full_name' => 'acme/hookify']);

        actingAs($user)->patch(route('projects.repository.update', $project), [
            'github_repo_id' => null,
            'github_repo_full_name' => null,
        ]);

        expect($project->fresh())
            ->github_repo_id->toBeNull()
            ->github_repo_full_name->toBeNull();
    });

    it('rejects a lone id without a name', function () {
        $user = User::factory()->create();
        [$project] = Project::createWithToken($user, 'Acme');

        actingAs($user)
            ->patch(route('projects.repository.update', $project), ['github_repo_id' => 42])
            ->assertSessionHasErrors('github_repo_full_name');
    });

    it('rejects a lone name without an id', function () {
        $user = User::factory()->create();
        [$project] = Project::createWithToken($user, 'Acme');

        actingAs($user)
            ->patch(route('projects.repository.update', $project), ['github_repo_full_name' => 'acme/hookify'])
            ->assertSessionHasErrors('github_repo_id');
    });
});
