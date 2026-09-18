<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $token = 'hk_live_'.Str::random(40);

        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'api_token_hash' => hash('sha256', $token),
            'api_token_prefix' => substr($token, 0, 16),
        ];
    }
}
