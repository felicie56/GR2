<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();

        if (!$user || !$user->hasRole($role)) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        return $next($request);
    }
}
