<?php

use App\Models\Project;
use App\Models\ProjectRule;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\withToken;

describe('Project deletion', function () {
    it('requires authentication', function () {
        [$project] = Project::createWithToken(User::factory()->create(), 'Acme');

        delete(route('projects.destroy', $project))->assertRedirect(route('login'));
    });

    it("returns 404 for another user's project", function () {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        [$project] = Project::createWithToken($owner, 'Acme');

        actingAs($stranger)->delete(route('projects.destroy', $project))->assertNotFound();
    });

    it('deletes the project and redirects to the index', function () {
        $user = User::factory()->create();
        [$project] = Project::createWithToken($user, 'Acme');

        actingAs($user)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        expect(Project::find($project->id))->toBeNull();
    });

    it('cascades deletion to its project_rules', function () {
        $user = User::factory()->create();
        [$project] = Project::createWithToken($user, 'Acme');
        $ruleCount = $project->rules()->count();
        expect($ruleCount)->toBeGreaterThan(0);

        actingAs($user)->delete(route('projects.destroy', $project));

        expect(ProjectRule::where('project_id', $project->id)->count())->toBe(0);
    });

    it('revokes the token immediately — the manifest endpoint stops authenticating', function () {
        [$project, $token] = Project::createWithToken(User::factory()->create(), 'Acme');

        actingAs($project->user)->delete(route('projects.destroy', $project));

        withToken($token)->getJson('/api/v1/manifest')->assertUnauthorized();
    });
});
