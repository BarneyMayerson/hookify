<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;

test('profile page is displayed', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('user can delete their account', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    assertGuest();
    expect($user->fresh())->toBeNull();
});
