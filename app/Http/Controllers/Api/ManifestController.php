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
        $preCommit = collect(ChecksCatalog::forIds($enabledIds))
            ->map(static fn (array $check, string $id): array => [
                'id' => $id,
                'run' => $check['command'],
            ])
            ->values()
            ->all();

        return response()->json([
            'schema_version' => self::SCHEMA_VERSION,
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
            ],
            'hooks' => [
                // Быстрые проверки — и только они. Тяжёлое идёт в pre-push/CI.
                'pre-commit' => $preCommit,
            ],
        ]);
    }
}
