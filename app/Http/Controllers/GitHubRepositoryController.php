<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GitHubRepositoryController extends Controller
{
    public const int PER_PAGE = 25;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $page = (int) ($validated['page'] ?? 1);

        /** @var User $user */
        $user = $request->user();

        if (! $user->github_token) {
            return response()->json([
                'message' => 'No GitHub token on file. Please reconnect your GitHub account.',
            ], 409);
        }

        try {
            // Without the `repo` scope GitHub returns only public repositories,
            // so no extra filtering is needed here.
            $response = Http::withToken($user->github_token)
                ->withHeaders([
                    'User-Agent' => 'Hookify-App',
                    'X-GitHub-Api-Version' => '2026-03-10',
                ])
                ->acceptJson()
                ->timeout(10)
                ->get('https://api.github.com/user/repos', [
                    'sort' => 'updated',
                    'per_page' => self::PER_PAGE,
                    'page' => $page,
                    'affiliation' => 'owner,collaborator,organization_member',
                ]);
        } catch (ConnectionException) {
            return response()->json(['message' => 'Could not reach GitHub. Try again in a moment.'], 502);
        }

        if ($response->unauthorized()) {
            // Revoked or expired: forget the token so the UI offers a reconnect
            // instead of failing the same way on every attempt.
            $user->forceFill(['github_token' => null, 'github_scopes' => null])->save();

            return response()->json([
                'message' => 'GitHub token is no longer valid. Please reconnect your GitHub account.',
            ], 409);
        }

        if ($response->forbidden()) {
            // 403 is not only "bad token": it is also how GitHub reports rate limits.
            if ($this->isRateLimited($response)) {
                return response()->json([
                    'message' => 'GitHub rate limit reached. Try again in a few minutes.',
                ], 429);
            }

            return response()->json([
                'message' => 'GitHub denied access. Reconnect your account, or ask an organization owner to approve Hookify.',
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

        $hasMore = preg_match(
            '/<[^>]+>\s*;\s*rel="?next"?/i',
            (string) $response->header('Link'),
        ) === 1;

        return response()->json([
            'repositories' => $repositories,
            'page' => $page,
            'has_more' => $hasMore,
            // Lets the UI offer "Include private repositories" only when needed.
            'private_access' => $user->hasRepoAccess(),
        ]);
    }

    private function isRateLimited(Response $response): bool
    {
        return $response->header('X-RateLimit-Remaining') === '0'
            || $response->header('Retry-After') !== '';
    }
}
