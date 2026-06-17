<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\ShopService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    public function __construct(
        private ShopService $shopService
    ) {}

    public function index()
    {
        $shop = auth()->user()->shop;

        $withdrawals = Withdrawal::where('shop_id', $shop->id)
            ->latest()
            ->paginate(15);

        $availableBalance = $this->shopService->getAvailableBalance($shop);

        return Inertia::render('Shop/Withdrawals/Index', [
            'withdrawals' => $withdrawals,
            'availableBalance' => $availableBalance,
            'shop' => $shop,
        ]);
    }

    public function store(Request $request)
    {
        $shop = auth()->user()->shop;

        $validated = $request->validate([
            'amount' => 'required|numeric|min:50000',
            'bank_name' => 'required|string|max:100',
            'bank_account' => 'required|string|max:50',
            'account_holder' => 'required|string|max:255',
        ]);

        try {
            $withdrawal = $this->shopService->requestWithdrawal($shop, $validated);

            return redirect()->back()->with('success', 'Withdrawal request submitted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
