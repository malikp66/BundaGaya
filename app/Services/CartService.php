<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class CartService
{
    public function __construct(
        private CommissionService $commissionService,
    ) {}

    public function getOrCreateCart(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    public function addItem(User $user, Product $product, Carbon $startDate, Carbon $endDate, int $quantity = 1): CartItem
    {
        $cart = $this->getOrCreateCart($user);
        $days = CartItem::calculateDays($startDate, $endDate);

        if ($product->stock < $quantity) {
            throw new InsufficientStockException("Insufficient stock for {$product->name}. Available: {$product->stock}");
        }

        if ($product->status !== 'active') {
            throw new InsufficientStockException("Product {$product->name} is not available");
        }

        $existingItem = $cart->items()
            ->where('product_id', $product->id)
            ->where('start_date', $startDate->format('Y-m-d'))
            ->where('end_date', $endDate->format('Y-m-d'))
            ->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            
            if ($product->stock < $newQuantity) {
                throw new InsufficientStockException("Insufficient stock for {$product->name}. Available: {$product->stock}, Requested: {$newQuantity}");
            }
            
            $existingItem->update([
                'quantity' => $newQuantity,
                'subtotal' => $existingItem->calculateSubtotal(),
            ]);

            return $existingItem->refresh();
        }

        return $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'days' => $days,
            'price_per_day' => $product->price_per_day,
            'subtotal' => $product->price_per_day * $days * $quantity,
        ]);
    }

    public function removeItem(User $user, int $cartItemId): bool
    {
        $cart = $this->getOrCreateCart($user);

        return (bool) $cart->items()->where('id', $cartItemId)->delete();
    }

    public function updateItemQuantity(User $user, int $cartItemId, int $quantity): ?CartItem
    {
        $cart = $this->getOrCreateCart($user);
        $item = $cart->items()->where('id', $cartItemId)->first();

        if (!$item) {
            return null;
        }

        $product = $item->product;
        
        if ($product->stock < $quantity) {
            throw new InsufficientStockException("Insufficient stock for {$product->name}. Available: {$product->stock}, Requested: {$quantity}");
        }

        $item->update([
            'quantity' => $quantity,
            'subtotal' => $item->price_per_day * $item->days * $quantity,
        ]);

        return $item->refresh();
    }

    public function clearCart(User $user): void
    {
        $cart = $this->getOrCreateCart($user);
        $cart->clear();
    }

    public function getCartSummary(User $user): array
    {
        $cart = $this->getOrCreateCart($user);
        $items = $cart->items()->with('product.shop')->get();

        $groupedByShop = $items->groupBy(fn ($item) => $item->product->shop_id);

        $shops = [];
        foreach ($groupedByShop as $shopId => $shopItems) {
            $shop = $shopItems->first()->product->shop;
            $shops[] = [
                'shop' => $shop,
                'items' => $shopItems,
                'subtotal' => $shopItems->sum('subtotal'),
            ];
        }

        return [
            'cart' => $cart,
            'shops' => $shops,
            'total_items' => $items->sum('quantity'),
            'total' => $items->sum('subtotal'),
        ];
    }

    public function validateAvailability(CartItem $item): array
    {
        $product = $item->product;
        $errors = [];

        if ($product->stock < $item->quantity) {
            $errors[] = "Stock tidak cukup untuk {$product->name} (Available: {$product->stock}, Requested: {$item->quantity})";
        }

        if ($product->status !== 'active') {
            $errors[] = "Produk {$product->name} tidak tersedia";
        }

        return $errors;
    }
}
