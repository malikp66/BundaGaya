<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerOrShopOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !in_array($request->user()->role, ['customer', 'shop_owner'])) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
