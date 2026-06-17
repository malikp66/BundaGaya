<?php

namespace App\Services;

use App\Exceptions\CartEmptyException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOrderStatusException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private const VALID_STATUS_TRANSITIONS = [
        'pending_payment' => ['paid', 'cancelled'],
        'paid' => ['confirmed_by_owner', 'cancelled'],
        'confirmed_by_owner' => ['picked_up'],
        'picked_up' => ['returned'],
        'returned' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function __construct(
        private CommissionService $commissionService,
        private CartService $cartService,
        private NotificationService $notificationService,
    ) {}

    public function createFromCart(User $user, array $data = []): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $cartSummary = $this->cartService->getCartSummary($user);
            $cartItems = $cartSummary['cart']->items()->with('product.shop')->get();

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
            $adminFee = $this->commissionService->getAdminFee();

            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending_payment',
                'subtotal' => $subtotal,
                'total' => $subtotal + $adminFee,
                'admin_fee' => $adminFee,
                'payment_status' => 'pending',
                'address' => $data['address'] ?? $user->address,
                'phone' => $data['phone'] ?? $user->phone,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;
                $shop = $product->shop;
                $commissionRate = $shop->commission_rate;

                $calculation = $this->commissionService->calculateFromOrderItem(
                    $cartItem->price_per_day,
                    $cartItem->days,
                    $cartItem->quantity,
                    $commissionRate,
                );

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'shop_id' => $shop->id,
                    'quantity' => $cartItem->quantity,
                    'start_date' => $cartItem->start_date,
                    'end_date' => $cartItem->end_date,
                    'days' => $cartItem->days,
                    'price_per_day' => $cartItem->price_per_day,
                    'subtotal' => $calculation['subtotal'],
                    'commission_rate' => $calculation['commission_rate'],
                    'commission_fee' => $calculation['commission_fee'],
                    'net_amount' => $calculation['net_amount'],
                    'status' => 'pending',
                ]);

                $totalCommission += $calculation['commission_fee'];

                Transaction::create([
                    'order_id' => $order->id,
                    'order_item_id' => $orderItem->id,
                    'shop_id' => $shop->id,
                    'amount' => $calculation['subtotal'],
                    'commission_rate' => $calculation['commission_rate'],
                    'commission_fee' => $calculation['commission_fee'],
                    'net_amount' => $calculation['net_amount'],
                    'status' => 'pending',
                ]);
            }

            $order->update([
                'commission_fee' => $totalCommission,
            ]);

            $this->cartService->clearCart($user);

            $order = $order->refresh();

            // Send notification email
            $this->notificationService->sendOrderCreatedNotification($order);

            return $order;
        });
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

            foreach ($order->items as $item) {
                $item->update(['status' => 'paid']);
            }

            $order = $order->refresh();

            // Send notification email
            $this->notificationService->sendOrderStatusChangedNotification($order);

            return $order;
        });
    }

    public function confirmByOwner(Order $order): Order
    {
        $this->validateStatusTransition($order->status, 'confirmed_by_owner');

        return DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                
                if ($product->stock < $item->quantity) {
                    throw new InsufficientStockException("Insufficient stock for {$product->name}");
                }
                
                $product->decrement('stock', $item->quantity);
                $item->update(['status' => 'confirmed']);
            }

            $order->update([
                'status' => 'confirmed_by_owner',
                'confirmed_at' => now(),
            ]);

            $order = $order->refresh();

            // Send notification email
            $this->notificationService->sendOrderStatusChangedNotification($order);

            return $order;
        });
    }

    public function markAsPickedUp(Order $order): Order
    {
        $this->validateStatusTransition($order->status, 'picked_up');

        $order->update(['status' => 'picked_up']);

        foreach ($order->items as $item) {
            $item->update(['status' => 'picked_up']);
        }

        $order = $order->refresh();

        // Send notification email
        $this->notificationService->sendOrderStatusChangedNotification($order);

        return $order;
    }

    public function markAsReturned(Order $order): Order
    {
        $this->validateStatusTransition($order->status, 'returned');

        $order->update(['status' => 'returned']);

        foreach ($order->items as $item) {
            $item->update(['status' => 'returned']);
            $item->product->increment('stock', $item->quantity);
            $item->product->increment('rental_count', $item->quantity);
        }

        $order = $order->refresh();

        // Send notification email
        $this->notificationService->sendOrderStatusChangedNotification($order);

        return $order;
    }

    public function complete(Order $order): Order
    {
        $this->validateStatusTransition($order->status, 'completed');

        return DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            foreach ($order->items as $item) {
                $item->update(['status' => 'completed']);
            }

            $order->transactions()->where('status', 'pending')->each(function ($transaction) {
                $transaction->settle();
            });

            $order = $order->refresh();

            // Send notification email
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

            foreach ($order->items as $item) {
                $item->update(['status' => 'cancelled']);
            }

            $order->transactions()->where('status', 'pending')->each(function ($transaction) {
                $transaction->update(['status' => 'cancelled']);
            });

            $order = $order->refresh();

            // Send notification email
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
