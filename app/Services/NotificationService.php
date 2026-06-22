<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Withdrawal;
use App\Models\ConsignorPayout;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        private WhatsAppService $whatsappService
    ) {}

    /**
     * Send order created notification
     *
     * @param Order $order
     * @return void
     */
    public function sendOrderCreatedNotification(Order $order): void
    {
        if (!$order->user->phone) {
            Log::warning('Cannot send order created notification: user has no phone number', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
            ]);
            return;
        }

        $result = $this->whatsappService->sendOrderCreatedNotification($order);
        
        if (!$result['success']) {
            Log::error('Failed to send order created notification', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'error' => $result['message'],
            ]);
        }
    }

    /**
     * Send order status changed notification
     *
     * @param Order $order
     * @return void
     */
    public function sendOrderStatusChangedNotification(Order $order): void
    {
        if (!$order->user->phone) {
            Log::warning('Cannot send order status notification: user has no phone number', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
            ]);
            return;
        }

        $statusLabels = [
            'pending_payment' => 'Menunggu Pembayaran',
            'paid' => 'Dibayar',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'in_use' => 'Sedang Digunakan',
            'returned' => 'Dikembalikan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $statusLabel = $statusLabels[$order->status] ?? $order->status;

        $result = $this->whatsappService->sendOrderStatusChangedNotification($order, $statusLabel);
        
        if (!$result['success']) {
            Log::error('Failed to send order status notification', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'status' => $order->status,
                'error' => $result['message'],
            ]);
        }
    }

    /**
     * Send payment received notification
     *
     * @param Order $order
     * @param Payment $payment
     * @return void
     */
    public function sendPaymentReceivedNotification(Order $order, Payment $payment): void
    {
        if (!$order->user->phone) {
            Log::warning('Cannot send payment received notification: user has no phone number', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
            ]);
            return;
        }

        $result = $this->whatsappService->sendPaymentReceivedNotification($order, $payment);
        
        if (!$result['success']) {
            Log::error('Failed to send payment received notification', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'payment_id' => $payment->id,
                'error' => $result['message'],
            ]);
        }
    }

    /**
     * Send withdrawal approved notification
     *
     * @param Withdrawal $withdrawal
     * @return void
     */
    public function sendWithdrawalApprovedNotification(Withdrawal $withdrawal): void
    {
        if (!$withdrawal->user->phone) {
            Log::warning('Cannot send withdrawal approved notification: user has no phone number', [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $withdrawal->user_id,
            ]);
            return;
        }

        $result = $this->whatsappService->sendWithdrawalApprovedNotification($withdrawal);
        
        if (!$result['success']) {
            Log::error('Failed to send withdrawal approved notification', [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $withdrawal->user_id,
                'error' => $result['message'],
            ]);
        }
    }

    /**
     * Send withdrawal rejected notification
     *
     * @param Withdrawal $withdrawal
     * @return void
     */
    public function sendWithdrawalRejectedNotification(Withdrawal $withdrawal): void
    {
        if (!$withdrawal->user->phone) {
            Log::warning('Cannot send withdrawal rejected notification: user has no phone number', [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $withdrawal->user_id,
            ]);
            return;
        }

        $result = $this->whatsappService->sendWithdrawalRejectedNotification($withdrawal);
        
        if (!$result['success']) {
            Log::error('Failed to send withdrawal rejected notification', [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $withdrawal->user_id,
                'error' => $result['message'],
            ]);
        }
    }

    public function sendPayoutRequestNotification(ConsignorPayout $payout): void
    {
        if (!$payout->user->phone) {
            Log::warning('Cannot send payout request notification: user has no phone number', [
                'payout_id' => $payout->id,
                'user_id' => $payout->user_id,
            ]);
            return;
        }

        $result = $this->whatsappService->sendPayoutRequestNotification($payout);

        if (!$result['success']) {
            Log::error('Failed to send payout request notification', [
                'payout_id' => $payout->id,
                'user_id' => $payout->user_id,
                'error' => $result['message'],
            ]);
        }
    }

    public function sendPayoutApprovedNotification(ConsignorPayout $payout): void
    {
        if (!$payout->user->phone) {
            Log::warning('Cannot send payout approved notification: user has no phone number', [
                'payout_id' => $payout->id,
                'user_id' => $payout->user_id,
            ]);
            return;
        }

        $result = $this->whatsappService->sendPayoutApprovedNotification($payout);

        if (!$result['success']) {
            Log::error('Failed to send payout approved notification', [
                'payout_id' => $payout->id,
                'user_id' => $payout->user_id,
                'error' => $result['message'],
            ]);
        }
    }

    public function sendPayoutRejectedNotification(ConsignorPayout $payout): void
    {
        if (!$payout->user->phone) {
            Log::warning('Cannot send payout rejected notification: user has no phone number', [
                'payout_id' => $payout->id,
                'user_id' => $payout->user_id,
            ]);
            return;
        }

        $result = $this->whatsappService->sendPayoutRejectedNotification($payout);

        if (!$result['success']) {
            Log::error('Failed to send payout rejected notification', [
                'payout_id' => $payout->id,
                'user_id' => $payout->user_id,
                'error' => $result['message'],
            ]);
        }
    }
}
