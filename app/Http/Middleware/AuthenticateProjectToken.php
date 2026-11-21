<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\TokenRejection;
use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateProjectToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'reason' => TokenRejection::InvalidToken->value,
            ], 401);
        }

        $project = Project::findByPlaintextToken($token);

        if ($project === null) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'reason' => Project::rejectionReason($token)->value,
            ], 401);
        }

        $request->attributes->set('project', $project);

        return $next($request);
    }
}
