<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\ShopService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function __construct(
        private ShopService $shopService
    ) {}

    public function index(Request $request)
    {
        $shop = auth()->user()->shop;

        $query = Transaction::where('shop_id', $shop->id)
            ->with(['order', 'orderItem.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->latest()->paginate(15);
        $revenue = $this->shopService->getShopRevenue($shop, $request->start_date, $request->end_date);

        return Inertia::render('Shop/Transactions/Index', [
            'transactions' => $transactions,
            'revenue' => $revenue,
            'filters' => $request->only(['status', 'start_date', 'end_date']),
            'shop' => $shop,
        ]);
    }
}
