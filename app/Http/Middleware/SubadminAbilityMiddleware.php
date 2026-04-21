<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubadminAbilityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'subadmin') {
            return $next($request);
        }

        if (($user->ability ?? 'fullaccess') === 'fullaccess') {
            return $next($request);
        }

        $routeName = (string) optional($request->route())->getName();
        $isReadMethod = in_array($request->method(), ['GET', 'HEAD'], true);

        // View-only subadmins: no mutating operations and no add/edit forms.
        if (! $isReadMethod || str_contains($routeName, '.create') || str_contains($routeName, '.edit')) {
            abort(403, 'You have view-only access.');
        }

        return $next($request);
    }
}
