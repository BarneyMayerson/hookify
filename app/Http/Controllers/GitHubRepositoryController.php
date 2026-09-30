<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GitHubRepositoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->github_token) {
            return response()->json([
                'message' => 'No GitHub token on file. Please reconnect your GitHub account.',
            ], 409);
        }

        $response = Http::withToken($user->github_token)
            ->acceptJson()
            ->get('https://api.github.com/user/repos', [
                'sort' => 'updated',
                'per_page' => 100,
                'affiliation' => 'owner,collaborator,organization_member',
            ]);

        if ($response->unauthorized() || $response->forbidden()) {
            return response()->json([
                'message' => 'GitHub token is no longer valid. Please reconnect your GitHub account.',
            ], 409);
        }

        if ($response->failed()) {
            return response()->json(['message' => 'Failed to fetch repositories from GitHub.'], 502);
        }

        /** @var list<array<string, mixed>> $repos */
        $repos = $response->json();

        $repositories = collect($repos)
            ->map(fn (array $repo) => [
                'id' => $repo['id'],
                'full_name' => $repo['full_name'],
                'private' => $repo['private'],
            ])
            ->values();

        return response()->json(['repositories' => $repositories]);
    }
}
