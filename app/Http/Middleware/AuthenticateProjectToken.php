<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateProjectToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null || ($project = Project::findByPlaintextToken($token)) === null) {
            return response()->json(['message' => 'Invalid project token.'], 401);
        }

        $request->attributes->set('project', $project);

        return $next($request);
    }
}
