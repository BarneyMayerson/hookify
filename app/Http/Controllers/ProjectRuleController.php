<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Support\ChecksCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class ProjectRuleController extends Controller
{
    public function update(Request $request, Project $project): RedirectResponse
    {
        // 404, не 403 — не подтверждаем чужому пользователю сам факт
        // существования проекта с таким id.
        abort_unless($project->user_id === $request->user()->id, 404);

        $validated = $request->validate([
            // 'present', не 'sometimes': пустой массив — осознанный выбор
            // "выключить все чеки", а не "поле не передали".
            'check_ids' => ['present', 'array'],
            'check_ids.*' => ['string', Rule::in(array_keys(ChecksCatalog::all()))],
        ]);

        /** @var list<string> $checkIds */
        $checkIds = $validated['check_ids'];

        $project->rules()->delete();
        $project->rules()->createMany(
            collect($checkIds)
                ->unique()
                ->map(fn (string $id) => ['check_id' => $id])
                ->all(),
        );

        return Redirect::route('projects.show', $project);
    }
}
