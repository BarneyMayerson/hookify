<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\ChecksCatalog;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
        // Явный ORDER BY — без него MySQL не гарантирует порядок.
        return $this->hasMany(ProjectRule::class)->orderBy('id');
    }

    /**
     * Создаёт проект и возвращает [проект, plaintext-токен].
     * Токен показывается пользователю ровно один раз — в БД лежит только хеш.
     * Новому проекту сразу засеваются дефолтные чеки из каталога — иначе
     * ManifestController отдал бы пустой pre-commit до первого визита в конструктор.
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
}
