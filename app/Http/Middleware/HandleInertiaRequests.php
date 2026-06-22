<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'display_name' => $user->display_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'is_guest' => (bool) $user->is_guest,
                    'role' => $user->role,
                ] : null,
            ],
            'app' => [
                'name' => config('app.name', 'BundaGaya'),
                'description' => 'Sewa baju kondangan branded dengan harga terjangkau. Gaun pesta, kebaya, tas Myzasac, dan aksesoris lengkap.',
                'url' => config('app.url'),
            ],
            'seo' => [
                'title' => null,
                'description' => null,
                'og_image' => null,
                'og_type' => 'website',
            ],
        ];
    }
}
