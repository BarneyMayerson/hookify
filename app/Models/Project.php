<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\ChecksCatalog;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property Carbon|null $last_synced_at
 */
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $hidden = ['api_token_hash'];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ProjectRule, $this>
     */
    public function rules(): HasMany
    {
        // Explicit ORDER BY — without it MySQL doesn't guarantee insertion
        // order on SELECT, and the constructor UI and this test rely on it.
        return $this->hasMany(ProjectRule::class)->orderBy('id');
    }

    /**
     * Creates a project and returns [project, plaintext token].
     * The token is shown to the user exactly once — only its hash is stored.
     * A new project is immediately seeded with the catalog's default checks —
     * otherwise ManifestController would return an empty pre-commit hook
     * until the user's first visit to the constructor.
     *
     * @return array{0: self, 1: string}
     */
    public static function createWithToken(User $user, string $name): array
    {
        $token = 'hk_live_'.Str::random(40);

        $project = new self;
        $project->forceFill([
            'user_id' => $user->id,
            'name' => $name,
            'api_token_hash' => hash('sha256', $token),
            'api_token_prefix' => substr($token, 0, 16),
        ])->save();

        $project->rules()->createMany(
            collect(ChecksCatalog::defaults())
                ->map(fn (string $checkId) => ['check_id' => $checkId])
                ->all(),
        );

        return [$project, $token];
    }

    public static function findByPlaintextToken(string $token): ?self
    {
        return static::where('api_token_hash', hash('sha256', $token))->first();
    }

    /**
     * Replaces the project's token. The old one stops working immediately,
     * because requests are matched by hash. Returns the new plaintext token,
     * which is shown to the user exactly once.
     */
    public function regenerateToken(): string
    {
        [$token, $tokenColumns] = self::issueToken();

        $this->forceFill($tokenColumns)->save();

        return $token;
    }

    /**
     * A fresh token and the columns to store for it: only the hash and a short
     * display prefix are persisted, never the token itself.
     *
     * @return array{0: string, 1: array{api_token_hash: string, api_token_prefix: string}}
     */
    private static function issueToken(): array
    {
        $token = 'hk_live_'.Str::random(40);

        return [$token, [
            'api_token_hash' => hash('sha256', $token),
            'api_token_prefix' => substr($token, 0, 16),
        ]];
    }
}
