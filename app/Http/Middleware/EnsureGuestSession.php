<?php

namespace App\Http\Middleware;

use App\Services\GuestAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class EnsureGuestSession
{
    public function __construct(
        private GuestAuthService $guests
    ) {}

    /**
     * Make sure every public-facing request resolves to a logged-in user.
     *
     * Routes that explicitly opt out (Filament /admin, logout, etc.) should
     * be excluded from this middleware in bootstrap/app.php. The middleware
     * never blocks a request — it just guarantees $request->user() is set so
     * downstream code can rely on it.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->guests->resolve($request);

        /** @var Response $response */
        $response = $next($request);

        $cookie = $request->attributes->get(GuestAuthService::COOKIE_NAME . ':cookie');

        if ($cookie instanceof Cookie) {
            $response->headers->setCookie($cookie);
        }

        return $response;
    }
}
