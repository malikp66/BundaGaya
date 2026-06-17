<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request)
    {
        $shop = auth()->user()->shop;

        $query = OrderItem::where('shop_id', $shop->id)
            ->with(['order.user', 'product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orderItems = $query->latest()->paginate(15);

        return Inertia::render('Shop/Orders/Index', [
            'orderItems' => $orderItems,
            'filters' => $request->only(['status']),
            'shop' => $shop,
        ]);
    }

    public function show(Order $order)
    {
        $shop = auth()->user()->shop;

        $orderItems = OrderItem::where('order_id', $order->id)
            ->where('shop_id', $shop->id)
            ->with(['product', 'order.user'])
            ->get();

        if ($orderItems->isEmpty()) {
            abort(403);
        }

        $order->load(['user', 'payment']);

        return Inertia::render('Shop/Orders/Show', [
            'order' => $order,
            'orderItems' => $orderItems,
            'shop' => $shop,
        ]);
    }

    public function confirm(Order $order)
    {
        $shop = auth()->user()->shop;

        $hasShopItems = OrderItem::where('order_id', $order->id)
            ->where('shop_id', $shop->id)
            ->exists();

        if (!$hasShopItems) {
            abort(403);
        }

        $this->orderService->confirmByOwner($order);

        return redirect()->back()->with('success', 'Order confirmed');
    }

    public function markPickedUp(Order $order)
    {
        $shop = auth()->user()->shop;

        $hasShopItems = OrderItem::where('order_id', $order->id)
            ->where('shop_id', $shop->id)
            ->exists();

        if (!$hasShopItems) {
            abort(403);
        }

        $this->orderService->markAsPickedUp($order);

        return redirect()->back()->with('success', 'Order marked as picked up');
    }

    public function markReturned(Order $order)
    {
        $shop = auth()->user()->shop;

        $hasShopItems = OrderItem::where('order_id', $order->id)
            ->where('shop_id', $shop->id)
            ->exists();

        if (!$hasShopItems) {
            abort(403);
        }

        $this->orderService->markAsReturned($order);

        return redirect()->back()->with('success', 'Order marked as returned');
    }
}
