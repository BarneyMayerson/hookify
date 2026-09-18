<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    /**
     * v0.1: пресет захардкожен. Таблица project_rules появится в v1.0
     * вместе с конструктором — до этого персистить нечего.
     *
     * Версия схемы манифеста передаётся явно: CLI старой версии должен уметь
     * понять, что сервер отдал формат, который он не умеет читать.
     */
    private const int SCHEMA_VERSION = 1;

    public function show(Request $request): JsonResponse
    {
        /** @var Project $project */
        $project = $request->attributes->get('project');

        $project->forceFill(['last_synced_at' => now()])->save();

        return response()->json([
            'schema_version' => self::SCHEMA_VERSION,
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
            ],
            'hooks' => [
                'pre-commit' => [
                    // Быстрые проверки — и только они. Тяжёлое идёт в pre-push/CI.
                    ['id' => 'pint', 'run' => 'vendor/bin/pint --dirty --test'],
                    ['id' => 'oxlint', 'run' => 'npx --no-install oxlint'],
                    ['id' => 'oxfmt', 'run' => 'npx --no-install oxfmt --check .'],
                ],
            ],
        ]);
    }
}
