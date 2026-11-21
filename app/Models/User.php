<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $github_id
 * @property string|null $github_nickname
 * @property string|null $github_avatar
 * @property string|null $github_token
 * @property list<string>|null $github_scopes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Hidden(['password', 'remember_token', 'github_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            // Grants repo-scoped access to the user's GitHub account —
            // must never be stored in plaintext.
            'github_token' => 'encrypted',
            // Scopes GitHub actually granted with this token.
            'github_scopes' => 'array',
        ];

    }

    protected static function booted(): void
    {
        // The projects are removed by the foreign key's cascade, which fires no
        // model events: retire their tokens here.
        static::deleting(function (self $user): void {
            $user->projects->each->retireCurrentToken();
        });
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function hasRepoAccess(): bool
    {
        return in_array('repo', $this->github_scopes ?? [], true);
    }
}
