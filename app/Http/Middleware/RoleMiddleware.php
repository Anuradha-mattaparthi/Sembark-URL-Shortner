<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class RoleMiddleware
{

    public function handle(Request $request, Closure $next, $roles = null): Response
    {
        $user = $request->user();


        if (! $user) {
            return redirect()->route('login');
        }


        if (! $roles) {
            return $next($request);
        }

        $allowed = array_map('trim', explode('|', $roles));
        $allowed = array_map(fn($r) => Str::lower($r), $allowed);

        $userRole = Str::lower($user->role ?? '');

        if (! in_array($userRole, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
