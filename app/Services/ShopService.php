<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class ShopService
{
    public function __construct(
        private CommissionService $commissionService,
        private NotificationService $notificationService,
    ) {}

    public function createShop(User $user, array $data): Shop
    {
        return $user->shop()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'province' => $data['province'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'status' => 'pending',
            'is_verified' => false,
            'commission_rate' => $this->commissionService->getDefaultCommissionRate(),
        ]);
    }

    public function approveShop(Shop $shop): Shop
    {
        $shop->update([
            'status' => 'active',
            'is_verified' => true,
            'rejection_reason' => null,
        ]);

        $shop->user->update(['role' => 'shop_owner']);

        $shop = $shop->refresh();

        // Send notification email
        $this->notificationService->sendShopApprovedNotification($shop);

        return $shop;
    }

    public function rejectShop(Shop $shop, string $reason): Shop
    {
        $shop->update([
            'status' => 'rejected',
            'is_verified' => false,
            'rejection_reason' => $reason,
        ]);

        $shop = $shop->refresh();

        // Send notification email
        $this->notificationService->sendShopRejectedNotification($shop);

        return $shop;
    }

    public function getShopRevenue(Shop $shop, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Transaction::where('shop_id', $shop->id);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $query->get();

        return [
            'total_revenue' => $transactions->where('status', 'settled')->sum('net_amount'),
            'total_commission' => $transactions->where('status', 'settled')->sum('commission_fee'),
            'total_transactions' => $transactions->count(),
            'pending_amount' => $transactions->where('status', 'pending')->sum('net_amount'),
            'settled_amount' => $transactions->where('status', 'settled')->sum('net_amount'),
            'withdrawn_amount' => Withdrawal::where('shop_id', $shop->id)
                ->whereIn('status', ['approved', 'processed'])
                ->sum('amount'),
        ];
    }

    public function getAvailableBalance(Shop $shop): float
    {
        $revenue = $this->getShopRevenue($shop);

        return $revenue['settled_amount'] - $revenue['withdrawn_amount'];
    }

    public function requestWithdrawal(Shop $shop, array $data): Withdrawal
    {
        $availableBalance = $this->getAvailableBalance($shop);

        if ($data['amount'] > $availableBalance) {
            throw new \Exception('Insufficient balance. Available: Rp ' . number_format($availableBalance, 0, ',', '.'));
        }

        return Withdrawal::create([
            'shop_id' => $shop->id,
            'user_id' => $shop->user_id,
            'amount' => $data['amount'],
            'bank_name' => $data['bank_name'],
            'bank_account' => $data['bank_account'],
            'account_holder' => $data['account_holder'],
            'status' => 'pending',
        ]);
    }

    public function processWithdrawal(Withdrawal $withdrawal, int $approvedBy): Withdrawal
    {
        $withdrawal->approve($approvedBy);

        $withdrawal = $withdrawal->refresh();

        // Send notification email
        $this->notificationService->sendWithdrawalApprovedNotification($withdrawal);

        return $withdrawal;
    }

    public function rejectWithdrawal(Withdrawal $withdrawal, string $reason, int $approvedBy): Withdrawal
    {
        $withdrawal->reject($reason, $approvedBy);

        $withdrawal = $withdrawal->refresh();

        // Send notification email
        $this->notificationService->sendWithdrawalRejectedNotification($withdrawal);

        return $withdrawal;
    }

    public function getShopStats(Shop $shop): array
    {
        return [
            'total_products' => $shop->products()->count(),
            'active_products' => $shop->products()->where('status', 'active')->count(),
            'total_orders' => $shop->orderItems()->count(),
            'pending_orders' => $shop->orderItems()->where('status', 'pending')->count(),
            'completed_orders' => $shop->orderItems()->where('status', 'completed')->count(),
            'total_revenue' => Transaction::where('shop_id', $shop->id)->where('status', 'settled')->sum('net_amount'),
            'average_rating' => $shop->products()->avg('rating_average') ?? 0,
        ];
    }
}
