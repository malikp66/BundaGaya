<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();

        $ordersCount = $user
            ? Order::query()->where('user_id', $user->id)->count()
            : 0;

        $tokenCreatedAt = null;
        $tokenLastUsedAt = null;

        if ($user) {
            $token = $user->tokens()->latest('created_at')->first();
            $tokenCreatedAt = $token?->created_at;
            $tokenLastUsedAt = $token?->last_used_at;
        }

        return Inertia::render('Account/Index', [
            'profile' => [
                'name' => $user?->name,
                'display_name' => $user?->display_name,
                'email' => $user?->email,
                'phone' => $user?->phone,
                'address' => $user?->address,
                'is_guest' => (bool) $user?->is_guest,
                'created_at' => $user?->created_at,
                'last_active_at' => $user?->last_active_at,
                'token_created_at' => $tokenCreatedAt,
                'token_last_used_at' => $tokenLastUsedAt,
            ],
            'stats' => [
                'orders_count' => $ordersCount,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $user->fill([
            'display_name' => $validated['name'] ?? $user->display_name,
            'phone' => $validated['phone'] ?? $user->phone,
            'address' => $validated['address'] ?? $user->address,
        ]);
        $user->save();

        return redirect()
            ->route('account.show')
            ->with('success', 'Identitas berhasil diperbarui.');
    }
}
