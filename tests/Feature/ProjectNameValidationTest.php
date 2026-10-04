<?php

use App\Models\Project;
use App\Models\User;

use function Pest\Laravel\actingAs;

describe('Project name validation', function () {
    it('rejects a name containing a newline', function () {
        $user = User::factory()->create();

        actingAs($user)
            ->post(route('projects.store'), ['name' => "Evil\nrm -rf ~"])
            ->assertSessionHasErrors('name');

        expect(Project::count())->toBe(0);
    });

    it('rejects a name containing a carriage return', function () {
        $user = User::factory()->create();

        actingAs($user)
            ->post(route('projects.store'), ['name' => "Evil\rsomething"])
            ->assertSessionHasErrors('name');
    });

    it('rejects a name containing a null byte', function () {
        $user = User::factory()->create();

        actingAs($user)
            ->post(route('projects.store'), ['name' => "Evil\0byte"])
            ->assertSessionHasErrors('name');
    });

    it('accepts an ordinary name with punctuation', function () {
        $user = User::factory()->create();

        actingAs($user)
            ->post(route('projects.store'), ['name' => 'Acme, Inc. — v2.0'])
            ->assertSessionHasNoErrors();

        expect(Project::first()->name)->toBe('Acme, Inc. — v2.0');
    });
});
