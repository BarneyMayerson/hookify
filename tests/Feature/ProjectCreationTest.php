<?php

use App\Models\Project;
use App\Models\User;
use App\Support\ChecksCatalog;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('Project creation', function () {
    it('requires authentication', function () {
        post(route('projects.store'), ['name' => 'Acme'])->assertRedirect(route('login'));
    });

    it('creates a project and flashes a one-time token', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->post(route('projects.store'), ['name' => 'Acme']);

        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('plaintext_token');

        $token = session('plaintext_token');
        expect($token)->toStartWith('hk_live_');

        $project = Project::where('user_id', $user->id)->first();
        expect($project->name)->toBe('Acme')
            ->and($project->api_token_hash)->toBe(hash('sha256', $token));
    });

    it('seeds the default checks from the catalog', function () {
        $user = User::factory()->create();

        actingAs($user)->post(route('projects.store'), ['name' => 'Acme']);

        $project = Project::where('user_id', $user->id)->first();
        expect($project->rules()->pluck('check_id')->all())->toBe(ChecksCatalog::defaults());
    });

    it('rejects an empty name', function () {
        $user = User::factory()->create();

        actingAs($user)
            ->post(route('projects.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        expect(Project::count())->toBe(0);
    });

    it('does not leak the token on a plain visit to the index', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->get(route('projects.index'));

        $response->assertInertia(
            fn ($page) => $page->component('Projects/Index')->where('flash.plaintext_token', null),
        );
    });

    it('only lists the current user\'s projects', function () {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        Project::createWithToken($stranger, 'Not mine');
        [$mine] = Project::createWithToken($user, 'Mine');

        $response = actingAs($user)->get(route('projects.index'));

        $response->assertInertia(
            fn ($page) => $page
                ->component('Projects/Index')
                ->has('projects', 1)
                ->where('projects.0.id', $mine->id),
        );
    });
});
