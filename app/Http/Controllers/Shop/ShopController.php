<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\ShopService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function __construct(
        private ShopService $shopService
    ) {}

    public function create()
    {
        if (auth()->user()->shop) {
            return redirect()->route('shop.dashboard');
        }

        return Inertia::render('Shop/Create');
    }

    public function store(Request $request)
    {
        if (auth()->user()->shop) {
            return redirect()->route('shop.dashboard');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
        ]);

        $shop = $this->shopService->createShop(auth()->user(), $validated);

        return redirect()->route('shop.dashboard')->with('success', 'Shop created successfully. Waiting for admin approval.');
    }

    public function show(Shop $shop)
    {
        $shop->load(['products' => function ($query) {
            $query->where('status', 'active')
                ->with(['category', 'brand', 'primaryPhoto']);
        }]);

        return Inertia::render('Customer/Shops/Show', [
            'shop' => $shop,
        ]);
    }
}
