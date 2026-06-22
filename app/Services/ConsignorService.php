<?php

namespace App\Services;

use App\Models\ConsignorBalance;
use App\Models\ConsignorPayout;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConsignorService
{
    public function __construct(
        private NotificationService $notificationService,
        private CommissionService $commissionService,
    ) {}

    public function getOrCreateBalance(User $user): ConsignorBalance
    {
        return ConsignorBalance::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'total_earned' => 0, 'total_withdrawn' => 0],
        );
    }

    public function getBalance(User $user): float
    {
        return $this->getOrCreateBalance($user)->balance;
    }

    public function creditEarnings(Order $order): void
    {
        $order->load('items');

        $consignorEarnings = [];

        foreach ($order->items as $item) {
            if (!$item->consignor_id) {
                continue;
            }

            $consignorId = $item->consignor_id;

            if (!isset($consignorEarnings[$consignorId])) {
                $consignorEarnings[$consignorId] = 0;
            }

            $consignorEarnings[$consignorId] += $item->net_amount;
        }

        foreach ($consignorEarnings as $userId => $earnings) {
            $user = User::find($userId);
            if (!$user) {
                continue;
            }

            $balance = $this->getOrCreateBalance($user);
            $balance->credit($earnings);
        }
    }

    public function requestPayout(User $user, float $amount, array $bankData): ConsignorPayout
    {
        $balance = $this->getOrCreateBalance($user);

        if ($balance->balance < $amount) {
            throw new \InvalidArgumentException('Insufficient balance');
        }

        return DB::transaction(function () use ($user, $balance, $amount, $bankData) {
            $payout = ConsignorPayout::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'bank_name' => $bankData['bank_name'],
                'bank_account_number' => $bankData['bank_account_number'],
                'bank_account_name' => $bankData['bank_account_name'],
                'status' => 'pending',
                'notes' => $bankData['notes'] ?? null,
            ]);

            $balance->debit($amount);

            $this->notificationService->sendPayoutRequestNotification($payout);

            return $payout;
        });
    }

    public function approvePayout(ConsignorPayout $payout, ?string $notes = null): ConsignorPayout
    {
        if (!$payout->isPending()) {
            throw new \InvalidArgumentException('Payout is not pending');
        }

        return DB::transaction(function () use ($payout, $notes) {
            $payout->update([
                'status' => 'approved',
                'approved_at' => now(),
                'notes' => $notes,
            ]);

            $this->notificationService->sendPayoutApprovedNotification($payout);

            return $payout->fresh();
        });
    }

    public function rejectPayout(ConsignorPayout $payout, ?string $reason = null): ConsignorPayout
    {
        if (!$payout->isPending()) {
            throw new \InvalidArgumentException('Payout is not pending');
        }

        return DB::transaction(function () use ($payout, $reason) {
            $payout->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'notes' => $reason,
            ]);

            $user = $payout->user;
            $balance = $this->getOrCreateBalance($user);
            $balance->credit($payout->amount);

            $this->notificationService->sendPayoutRejectedNotification($payout);

            return $payout->fresh();
        });
    }

    public function completePayout(ConsignorPayout $payout): ConsignorPayout
    {
        if (!$payout->isApproved()) {
            throw new \InvalidArgumentException('Payout is not approved');
        }

        $payout->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $payout->fresh();
    }

    public function getConsignorStats(User $user): array
    {
        $balance = $this->getOrCreateBalance($user);

        $totalProducts = $user->consignedProducts()->count();
        $activeProducts = $user->consignedProducts()->where('is_active', true)->count();
        $totalRented = $user->consignedProducts()->sum('rental_count');

        $pendingPayouts = ConsignorPayout::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');

        return [
            'balance' => $balance->balance,
            'total_earned' => $balance->total_earned,
            'total_withdrawn' => $balance->total_withdrawn,
            'pending_payouts' => $pendingPayouts,
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'total_rented' => $totalRented,
        ];
    }
}
