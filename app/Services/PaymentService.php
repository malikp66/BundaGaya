<?php

namespace App\Services;

use App\Exceptions\PaymentProcessingException;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private bool $isProduction;
    private string $serverKey;
    private string $clientKey;

    public function __construct()
    {
        $this->isProduction = config('midtrans.is_production', false);
        $this->serverKey = config('midtrans.server_key', '');
        $this->clientKey = config('midtrans.client_key', '');

        \Midtrans\Config::$serverKey = $this->serverKey;
        \Midtrans\Config::$clientKey = $this->clientKey;
        \Midtrans\Config::$isProduction = $this->isProduction;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    public function createSnapToken(Order $order): string
    {
        $payment = Payment::firstOrCreate(
            ['order_id' => $order->id],
            [
                'amount' => $order->total,
                'status' => 'pending',
            ],
        );

        if ($payment->snap_token && $payment->isPending()) {
            return $payment->snap_token;
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->phone ?? $order->user->phone,
            ],
            'item_details' => $order->items->map(fn ($item) => [
                'id' => $item->product_id,
                'price' => (int) $item->subtotal,
                'quantity' => 1,
                'name' => $item->product->name,
            ])->toArray(),
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            $payment->update([
                'snap_token' => $snapToken,
            ]);

            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());
            throw new PaymentProcessingException('Failed to create payment token: ' . $e->getMessage());
        }
    }

    public function handleNotification(array $notification): Payment
    {
        if (!$this->verifySignature($notification)) {
            throw new PaymentProcessingException('Invalid payment notification signature');
        }

        $orderId = $notification['order_id'] ?? null;
        $transactionId = $notification['transaction_id'] ?? null;
        $statusCode = $notification['status_code'] ?? null;
        $grossAmount = $notification['gross_amount'] ?? null;
        $paymentType = $notification['payment_type'] ?? null;

        if (!$orderId) {
            throw new PaymentProcessingException('Missing order_id in notification');
        }

        $order = Order::where('order_number', $orderId)->first();
        
        if (!$order) {
            throw new PaymentProcessingException("Order not found: {$orderId}");
        }

        $payment = Payment::where('order_id', $order->id)->first();
        
        if (!$payment) {
            throw new PaymentProcessingException("Payment record not found for order: {$orderId}");
        }

        if ($payment->status === 'paid') {
            Log::info("Payment notification already processed for order: {$orderId}");
            return $payment;
        }

        $payment->update([
            'transaction_id' => $transactionId,
            'method' => $paymentType,
            'gateway_response' => json_encode($notification),
        ]);

        if ($statusCode == '200' || $statusCode == '201') {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            app(OrderService::class)->markAsPaid($order);

            // Send payment received notification
            app(NotificationService::class)->sendPaymentReceivedNotification($order, $payment);
        } elseif ($statusCode == '202') {
            $payment->update(['status' => 'pending']);
        } else {
            $payment->update([
                'status' => 'failed',
                'expired_at' => now(),
            ]);
        }

        return $payment->refresh();
    }

    private function verifySignature(array $notification): bool
    {
        if (empty($this->serverKey)) {
            Log::warning('Midtrans server key not configured, skipping signature verification');
            return true;
        }

        $orderId = $notification['order_id'] ?? '';
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $signatureKey = $notification['signature_key'] ?? '';

        if (empty($signatureKey)) {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }

    public function isConfigured(): bool
    {
        return !empty($this->serverKey) && !empty($this->clientKey);
    }
}
