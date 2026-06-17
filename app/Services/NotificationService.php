<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\Withdrawal;
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
            'confirmed_by_owner' => 'Dikonfirmasi Pemilik Toko',
            'picked_up' => 'Diambil',
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
     * Send shop approved notification
     *
     * @param Shop $shop
     * @return void
     */
    public function sendShopApprovedNotification(Shop $shop): void
    {
        if (!$shop->user->phone) {
            Log::warning('Cannot send shop approved notification: user has no phone number', [
                'shop_id' => $shop->id,
                'user_id' => $shop->user_id,
            ]);
            return;
        }

        $result = $this->whatsappService->sendShopApprovedNotification($shop);
        
        if (!$result['success']) {
            Log::error('Failed to send shop approved notification', [
                'shop_id' => $shop->id,
                'user_id' => $shop->user_id,
                'error' => $result['message'],
            ]);
        }
    }

    /**
     * Send shop rejected notification
     *
     * @param Shop $shop
     * @return void
     */
    public function sendShopRejectedNotification(Shop $shop): void
    {
        if (!$shop->user->phone) {
            Log::warning('Cannot send shop rejected notification: user has no phone number', [
                'shop_id' => $shop->id,
                'user_id' => $shop->user_id,
            ]);
            return;
        }

        $result = $this->whatsappService->sendShopRejectedNotification($shop);
        
        if (!$result['success']) {
            Log::error('Failed to send shop rejected notification', [
                'shop_id' => $shop->id,
                'user_id' => $shop->user_id,
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
}
