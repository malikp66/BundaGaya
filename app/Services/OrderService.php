<?php

namespace App\Services;

use App\Exceptions\CartEmptyException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOrderStatusException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private const VALID_STATUS_TRANSITIONS = [
        'pending_payment' => ['paid', 'cancelled'],
        'paid' => ['processing', 'cancelled'],
        'processing' => ['shipped'],
        'shipped' => ['in_use'],
        'in_use' => ['returned'],
        'returned' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function __construct(
        private CommissionService $commissionService,
        private CartService $cartService,
        private NotificationService $notificationService,
        private ConsignorService $consignorService,
    ) {}

    public function createFromCart(User $user, array $data = []): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $cartSummary = $this->cartService->getCartSummary($user);
            $cartItems = $cartSummary['cart']->items()->with('product')->get();

            if ($cartItems->isEmpty()) {
                throw new CartEmptyException();
            }

            foreach ($cartItems as $item) {
                $errors = $this->cartService->validateAvailability($item);
                if (!empty($errors)) {
                    throw new InsufficientStockException(implode(', ', $errors));
                }
            }

            $subtotal = $cartItems->sum('subtotal');
            $totalCommission = 0;
            $totalDP = 0;
            $adminFee = $this->commissionService->getAdminFee();
            $shippingCost = (float) ($data['shipping_cost'] ?? 0);
            $returnDate = $cartItems->max('end_date') ?? now()->addDay()->format('Y-m-d');

            $order = Order::create([
                'user_id' => $user->id,
                'customer_name' => $data['customer_name'] ?? $user->name,
                'instagram' => $data['instagram'] ?? null,
                'status' => 'pending_payment',
                'subtotal' => $subtotal,
                'total' => $subtotal + $adminFee,
                'admin_fee' => $adminFee,
                'dp_total' => 0,
                'shipping_cost' => $shippingCost,
                'grand_total' => $subtotal + $adminFee + $shippingCost,
                'shipping_courier' => $data['shipping_courier'] ?? null,
                'shipping_service' => $data['shipping_service'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'city' => $data['city'] ?? null,
                'province' => $data['province'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'district' => $data['district'] ?? null,
                'suburb' => $data['suburb'] ?? null,
                'return_date' => $returnDate,
                'payment_status' => 'pending',
                'address' => $data['address'] ?? $user->address,
                'phone' => $data['phone'] ?? $user->phone,
                'notes' => $data['notes'] ?? null,
                'refund_bank_name' => $data['refund_bank_name'] ?? null,
                'refund_bank_account' => $data['refund_bank_account'] ?? null,
                'refund_bank_holder' => $data['refund_bank_holder'] ?? null,
                'dp_status' => 'pending',
            ]);

            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;
                $commissionRate = $this->commissionService->getDefaultCommissionRate();
                $dpPercentage = $product->dp_percentage ?? $this->commissionService->getDefaultDPPercentage();

                $calculation = $this->commissionService->calculateWithDP(
                    $cartItem->subtotal,
                    $commissionRate,
                    $dpPercentage,
                );

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'consignor_id' => $product->user_id,
                    'quantity' => $cartItem->quantity,
                    'start_date' => $cartItem->start_date,
                    'end_date' => $cartItem->end_date,
                    'days' => $cartItem->days,
                    'price_per_day' => $cartItem->price_per_day,
                    'subtotal' => $calculation['subtotal'],
                    'commission_rate' => $calculation['commission_rate'],
                    'commission_fee' => $calculation['commission_fee'],
                    'net_amount' => $calculation['net_amount'],
                    'dp_amount' => $calculation['dp_amount'],
                    'dp_percentage' => $calculation['dp_percentage'],
                    'status' => 'pending',
                ]);

                $totalCommission += $calculation['commission_fee'];
                $totalDP += $calculation['dp_amount'];
            }

            $order->update([
                'commission_fee' => $totalCommission,
                'dp_total' => $totalDP,
                'grand_total' => $subtotal + $adminFee + $shippingCost + $totalDP,
            ]);

            $this->cartService->clearCart($user);

            $this->syncGuestProfile($user, $data, $order);

            $order = $order->refresh();

            $this->notificationService->sendOrderCreatedNotification($order);

            return $order;
        });
    }

    /**
     * Persist any identity fields the user typed at checkout back onto the
     * guest account so future visits remember them. Safe to call for
     * non-guest accounts too — it only fills empty fields.
     */
    protected function syncGuestProfile(User $user, array $data, Order $order): void
    {
        if (!$user) {
            return;
        }

        $updates = [];

        if (empty($user->name) || str_starts_with((string) $user->name, 'Guest #')) {
            if (!empty($data['customer_name'])) {
                $updates['name'] = $data['customer_name'];
                $updates['display_name'] = $data['customer_name'];
            }
        }

        if (empty($user->phone) && !empty($data['phone'])) {
            $updates['phone'] = $data['phone'];
        }

        if (empty($user->address) && !empty($data['address'])) {
            $updates['address'] = $data['address'];
        }

        if (!empty($updates)) {
            $user->fill($updates)->save();
        }
    }

    public function markAsPaid(Order $order): Order
    {
        $this->validateStatusTransition($order->status, 'paid');

        return DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'paid',
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);

            $order->load('items');
            foreach ($order->items as $item) {
                $item->update(['status' => 'paid']);
            }

            $order = $order->refresh();

            $this->notificationService->sendOrderStatusChangedNotification($order);

            return $order;
        });
    }

    public function markAsProcessing(Order $order): Order
    {
        $this->validateStatusTransition($order->status, 'processing');

        return DB::transaction(function () use ($order) {
            $order->load('items');
            foreach ($order->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                if ($product->stock < $item->quantity) {
                    throw new InsufficientStockException("Insufficient stock for {$product->name}");
                }

                $product->decrement('stock', $item->quantity);
                $item->update(['status' => 'processing']);
            }

            $order->update([
                'status' => 'processing',
                'processed_at' => now(),
            ]);

            $order = $order->refresh();

            $this->notificationService->sendOrderStatusChangedNotification($order);

            return $order;
        });
    }

    public function markAsShipped(Order $order, string $trackingNumber, string $courier, string $service): Order
    {
        $this->validateStatusTransition($order->status, 'shipped');

        $order->update([
            'status' => 'shipped',
            'tracking_number' => $trackingNumber,
            'shipping_courier' => $courier,
            'shipping_service' => $service,
            'shipped_at' => now(),
        ]);

        $order->load('items');
        foreach ($order->items as $item) {
            $item->update(['status' => 'shipped']);
        }

        $order = $order->refresh();

        $this->notificationService->sendOrderStatusChangedNotification($order);

        return $order;
    }

    public function markAsInUse(Order $order): Order
    {
        $this->validateStatusTransition($order->status, 'in_use');

        $order->update(['status' => 'in_use']);

        $order->load('items');
        foreach ($order->items as $item) {
            $item->update(['status' => 'in_use']);
        }

        $order = $order->refresh();

        $this->notificationService->sendOrderStatusChangedNotification($order);

        return $order;
    }

    public function markAsReturned(Order $order): Order
    {
        $this->validateStatusTransition($order->status, 'returned');

        $order->update([
            'status' => 'returned',
            'returned_at' => now(),
        ]);

        $order->load('items.product');
        foreach ($order->items as $item) {
            $item->update(['status' => 'returned']);
            $item->product->increment('stock', $item->quantity);
            $item->product->increment('rental_count', $item->quantity);
        }

        $order = $order->refresh();

        $this->notificationService->sendOrderStatusChangedNotification($order);

        return $order;
    }

    public function completeOrder(Order $order): Order
    {
        $this->validateStatusTransition($order->status, 'completed');

        return DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
                'dp_refunded' => $order->dp_total - $order->dp_deducted,
            ]);

            $order->load('items');
            foreach ($order->items as $item) {
                $item->update(['status' => 'completed']);
            }

            $order->transactions()->where('status', 'pending')->each(function ($transaction) {
                $transaction->settle();
            });

            $this->consignorService->creditEarnings($order);

            $order = $order->refresh();

            $this->notificationService->sendOrderStatusChangedNotification($order);

            return $order;
        });
    }

    public function processDamage(Order $order, float $deductedAmount): Order
    {
        $this->validateStatusTransition($order->status, 'completed');

        return DB::transaction(function () use ($order, $deductedAmount) {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
                'dp_deducted' => $deductedAmount,
                'dp_refunded' => $order->dp_total - $deductedAmount,
            ]);

            $order->load('items');
            foreach ($order->items as $item) {
                $item->update(['status' => 'completed']);
            }

            $order->transactions()->where('status', 'pending')->each(function ($transaction) {
                $transaction->settle();
            });

            $this->consignorService->creditEarnings($order);

            $order = $order->refresh();

            $this->notificationService->sendOrderStatusChangedNotification($order);

            return $order;
        });
    }

    public function cancel(Order $order, string $reason = ''): Order
    {
        $this->validateStatusTransition($order->status, 'cancelled');

        return DB::transaction(function () use ($order, $reason) {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'notes' => trim(($order->notes ?? '') . ' ' . $reason),
            ]);

            $order->load('items');
            foreach ($order->items as $item) {
                $item->update(['status' => 'cancelled']);
            }

            $order->transactions()->where('status', 'pending')->each(function ($transaction) {
                $transaction->update(['status' => 'cancelled']);
            });

            $order = $order->refresh();

            $this->notificationService->sendOrderStatusChangedNotification($order);

            return $order;
        });
    }

    private function validateStatusTransition(string $currentStatus, string $newStatus): void
    {
        if (!isset(self::VALID_STATUS_TRANSITIONS[$currentStatus])) {
            throw new InvalidOrderStatusException("Unknown order status: {$currentStatus}");
        }

        if (!in_array($newStatus, self::VALID_STATUS_TRANSITIONS[$currentStatus])) {
            throw new InvalidOrderStatusException(
                "Cannot transition from {$currentStatus} to {$newStatus}"
            );
        }
    }
}
