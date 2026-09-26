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
        // 404, not 403 — don't confirm to another user that a project
        // with this id even exists.
        abort_unless($project->user_id === $request->user()->id, 404);

        $validated = $request->validate([
            // 'present', not 'sometimes': an empty array is a deliberate
            // "disable every check", not "the field wasn't sent".
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
