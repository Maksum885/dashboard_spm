<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        $allowed = [];
        foreach ($roles as $segment) {
            foreach (explode(',', $segment) as $r) {
                $allowed[] = trim($r);
            }
        }

        if (! $user || ! in_array($user->role, $allowed, true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
