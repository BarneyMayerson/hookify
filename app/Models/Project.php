<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TokenRejection;
use App\Support\ChecksCatalog;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @property string $api_token_hash
 * @property Carbon|null $last_synced_at
 */
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $hidden = ['api_token_hash'];

    protected static function booted(): void
    {
        // Whatever deletes a project (the dashboard, an account deletion), its
        // token must stay recognisable as "belonged to a deleted project".
        static::deleting(fn (self $project) => $project->retireCurrentToken());
    }

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
        [$token, $tokenColumns] = self::issueToken();

        $project = new self;
        $project->forceFill([
            'user_id' => $user->id,
            'name' => $name,
            ...$tokenColumns,
        ])->save();

        $project->rules()->createMany(
            collect(ChecksCatalog::defaults())
                ->map(fn (string $checkId) => ['check_id' => $checkId])
                ->all(),
        );

        return [$project, $token];
    }

    /**
     * Replaces the project's token. The old one stops working immediately,
     * because requests are matched by hash. Returns the new plaintext token,
     * which is shown to the user exactly once.
     */
    public function regenerateToken(): string
    {
        [$token, $tokenColumns] = self::issueToken();

        DB::transaction(function () use ($tokenColumns): void {
            $this->retireCurrentToken();
            $this->forceFill($tokenColumns)->save();
        });

        return $token;
    }

    /**
     * Remembers the hash of the current token, so that after the token is
     * replaced or the project is deleted it can still be told apart from one
     * that was never issued. Only a hash is kept, and the row has no foreign
     * key on purpose: it must outlive the project.
     */
    public function retireCurrentToken(): void
    {
        DB::table('retired_tokens')->insertOrIgnore([
            'token_hash' => $this->api_token_hash,
            'project_id' => $this->getKey(),
            'retired_at' => now(),
        ]);
    }

    /**
     * Why a token that matches no project is rejected: replaced by a newer
     * one, belonging to a deleted project, or unknown altogether. Meant for
     * the failure path only.
     */
    public static function rejectionReason(string $token): TokenRejection
    {
        $projectId = DB::table('retired_tokens')
            ->where('token_hash', hash('sha256', $token))
            ->value('project_id');

        if ($projectId === null) {
            return TokenRejection::InvalidToken;
        }

        return static::whereKey($projectId)->exists()
            ? TokenRejection::TokenRevoked
            : TokenRejection::ProjectDeleted;
    }

    public static function findByPlaintextToken(string $token): ?self
    {
        return static::where('api_token_hash', hash('sha256', $token))->first();
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
