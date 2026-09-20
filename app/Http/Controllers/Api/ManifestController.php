<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\ChecksCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    /**
     * Версия схемы манифеста передаётся явно: CLI старой версии должен уметь
     * понять, что сервер отдал формат, который он не умеет читать.
     */
    private const int SCHEMA_VERSION = 1;

    /**
     * Известные типы git-хуков. Новый тип (например, pre-push в v2.0)
     * добавляется сюда одной строкой — не трогая остальную логику метода.
     *
     * @var list<string>
     */
    private const array HOOK_TYPES = ['pre-commit', 'commit-msg'];

    public function show(Request $request): JsonResponse
    {
        /** @var Project $project */
        $project = $request->attributes->get('project');

        $project->forceFill(['last_synced_at' => now()])->save();

        /** @var list<string> $enabledIds */
        $enabledIds = $project->rules()->pluck('check_id')->all();

        // forIds сохраняет порядок каталога, а не порядок строк в БД —
        // манифест детерминирован независимо от того, в каком порядке
        // записи попали в project_rules.
        $enabledChecks = collect(ChecksCatalog::forIds($enabledIds))
            ->map(static fn (array $check, string $id): array => [
                'id' => $id,
                'run' => $check['command'],
                'hook' => $check['hook'],
            ])
            ->values();

        // Оба ключа хуков присутствуют всегда, даже пустыми списками —
        // CLI и фронт полагаются на стабильную форму JSON, а не на то,
        // что ключ есть только когда для него что-то включено.
        $hooks = collect(self::HOOK_TYPES)->mapWithKeys(
            static fn (string $hookName): array => [
                $hookName => $enabledChecks
                    ->where('hook', $hookName)
                    ->map(static fn (array $check): array => [
                        'id' => $check['id'],
                        'run' => $check['run'],
                    ])
                    ->values()
                    ->all(),
            ],
        );

        return response()->json([
            'schema_version' => self::SCHEMA_VERSION,
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
            ],
            // Быстрые проверки — и только они. Тяжёлое идёт в pre-push/CI.
            'hooks' => $hooks,
        ]);
    }
}
