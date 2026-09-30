<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

describe('User github_token encryption', function () {
    it('stores the github token encrypted at rest', function () {
        $user = User::factory()->create(['github_token' => 'gh_plaintext_token']);

        $raw = DB::table('users')->where('id', $user->id)->value('github_token');

        expect($raw)->not->toBe('gh_plaintext_token')
            ->and($user->fresh()->github_token)->toBe('gh_plaintext_token');
    });

    it('never exposes github_token when the model is serialized', function () {
        $user = User::factory()->create(['github_token' => 'gh_plaintext_token']);

        expect($user->toArray())->not->toHaveKey('github_token')
            ->and($user->toJson())->not->toContain('gh_plaintext_token');
    });
});
