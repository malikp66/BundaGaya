<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\ShopService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private ShopService $shopService
    ) {}

    public function index()
    {
        $shop = auth()->user()->shop;

        if (!$shop) {
            return Inertia::render('Shop/Dashboard', [
                'shop' => null,
                'stats' => null,
                'revenue' => null,
            ]);
        }

        $stats = $this->shopService->getShopStats($shop);
        $revenue = $this->shopService->getShopRevenue($shop);
        $availableBalance = $this->shopService->getAvailableBalance($shop);

        $recentOrders = OrderItem::where('shop_id', $shop->id)
            ->with(['order.user', 'product'])
            ->latest()
            ->limit(5)
            ->get();

        $topProducts = Product::where('shop_id', $shop->id)
            ->orderByDesc('rental_count')
            ->limit(5)
            ->get();

        return Inertia::render('Shop/Dashboard', [
            'shop' => $shop,
            'stats' => $stats,
            'revenue' => $revenue,
            'availableBalance' => $availableBalance,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
        ]);
    }
}
