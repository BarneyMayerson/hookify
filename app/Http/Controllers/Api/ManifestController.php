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
     * The manifest schema version is sent explicitly: an older CLI must be
     * able to tell that the server returned a format it can't read.
     */
    private const int SCHEMA_VERSION = 1;

    /**
     * Known git hook types. A new type (e.g. pre-push in v2.0) is added
     * here as a single line, without touching the rest of the method's logic.
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

        // forIds preserves catalog order, not DB row order — the manifest
        // is deterministic regardless of the order rows ended up in
        // project_rules.
        $enabledChecks = collect(ChecksCatalog::forIds($enabledIds))
            ->map(static fn (array $check, string $id): array => [
                'id' => $id,
                'run' => $check['command'],
                'hook' => $check['hook'],
            ])
            ->values();

        // Both hook keys are always present, even as empty lists — the CLI
        // and the frontend rely on a stable JSON shape, not on a key only
        // existing when something is enabled for it.
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
            // Fast checks only. Heavier ones belong in pre-push/CI.
            'hooks' => $hooks,
        ]);
    }
}
