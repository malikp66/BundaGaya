<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class GuestAuthService
{
    public const COOKIE_NAME = 'bg_guest_token';

    /**
     * Resolve or create a guest user for the current device.
     *
     * Strategy:
     *  - If the request already has an authenticated user (Filament admin or seeded user),
     *    return that user untouched.
     *  - Otherwise inspect the device-bound cookie. A valid token will be reused
     *    so the same browser keeps its cart/orders.
     *  - If the cookie is missing, expired, or points at a deleted user, a fresh
     *    guest user + long-lived personal access token is minted, and a refreshed
     *    cookie is queued on the response.
     */
    public function resolve(Request $request): User
    {
        if ($request->user()) {
            return $request->user();
        }

        $cookieToken = $request->cookie(self::COOKIE_NAME);

        if ($cookieToken) {
            $accessToken = $this->findUsableToken($cookieToken);

            if ($accessToken && $accessToken->tokenable instanceof User) {
                $user = $accessToken->tokenable;

                if (!$user->is_guest) {
                    $user->forceFill(['is_guest' => true])->save();
                }

                $this->login($user, $request);
                $this->touchLastActive($user);
                $this->refreshCookie($request, $cookieToken);

                return $user;
            }
        }

        return $this->provisionNewGuest($request);
    }

    /**
     * Find a usable token by its plain text value. Sanctum stores SHA-256 hashes,
     * so we must hash the cookie value first before querying.
     */
    protected function findUsableToken(string $plainTextToken): ?PersonalAccessToken
    {
        if (empty($plainTextToken)) {
            return null;
        }

        $hashed = hash('sha256', $plainTextToken);

        /** @var PersonalAccessToken|null $token */
        $token = PersonalAccessToken::query()
            ->where('token', $hashed)
            ->first();

        if (!$token) {
            return null;
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            return null;
        }

        if (!$token->tokenable instanceof User) {
            return null;
        }

        return $token;
    }

    /**
     * Create a brand new guest user + Sanctum personal access token, then set
     * the device cookie. The cookie is intentionally long-lived (10 years)
     * and httpOnly so it cannot be read or forged by client-side scripts.
     */
    protected function provisionNewGuest(Request $request): User
    {
        return DB::transaction(function () use ($request) {
            $user = User::query()->create([
                'name' => User::generateGuestLabel(),
                'display_name' => null,
                'email' => null,
                'password' => null,
                'role' => 'customer',
                'is_active' => true,
                'is_guest' => true,
                'last_active_at' => now(),
            ]);

            $plainText = Str::random(64);

            $user->createToken(
                name: 'guest-device',
                abilities: ['*'],
                expiresAt: null,
            )->forceFill([
                'token' => hash('sha256', $plainText),
            ])->save();

            $this->refreshCookie($request, $plainText);
            $this->login($user, $request);

            return $user->refresh();
        });
    }

    /**
     * Sign the user in for the remainder of the request lifecycle.
     *
     * We deliberately do NOT call auth()->guard('web')->setUser() or
     * auth()->login() here because doing so triggers the session-based
     * auth driver, which in turn can re-enter the request pipeline and
     * blow the execution time budget. Instead we attach the user
     * directly to the request via setUserResolver, which is what
     * `$request->user()` consults, and we also override the resolver
     * on the web guard so the `auth()->user()` helper used in
     * controllers returns the same guest user without re-resolving
     * the session.
     */
    protected function login(User $user, Request $request): void
    {
        $request->setUserResolver(fn () => $user);

        try {
            $guard = auth()->guard('web');
            $guard->setUserResolver(fn () => $user);
        } catch (\Throwable) {
            // best-effort; the request resolver is the primary path
        }
    }

    protected function touchLastActive(User $user): void
    {
        if (!$user->last_active_at || $user->last_active_at->lt(now()->subMinutes(5))) {
            $user->forceFill(['last_active_at' => now()])->save();
        }
    }

    /**
     * Queue a refreshed cookie on the response so the device session
     * never silently expires while the user keeps visiting.
     */
    protected function refreshCookie(Request $request, string $plainTextToken): void
    {
        $minutes = 60 * 24 * 365 * 10; // 10 years

        $cookie = cookie(
            self::COOKIE_NAME,
            $plainTextToken,
            $minutes,
            '/',
            null,
            config('session.secure', false),
            true, // httpOnly
            false,
            config('session.same_site', 'lax'),
        );

        // The response may not exist yet (we're inside the request pipeline),
        // so we attach via the global cookie queue instead of ->withCookie().
        $request->attributes->set(self::COOKIE_NAME . ':cookie', $cookie);
    }
}
