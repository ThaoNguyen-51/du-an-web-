<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || in_array(auth()->user()->role, ['admin', 'staff'], true)) {
            abort(403, 'Bạn không có quyền truy cập chức năng khách hàng.');
        }

        return $next($request);
    }
}
