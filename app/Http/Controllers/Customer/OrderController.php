<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $query = Order::with(['items.product', 'items.shop', 'payment'])
            ->where('user_id', auth()->id())
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(10);

        return Inertia::render('Customer/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status']),
        ]);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items.product.shop', 'payment', 'reviews']);

        return Inertia::render('Customer/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'phone' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $order = $this->orderService->createFromCart(auth()->user(), $request->only(['address', 'phone', 'notes']));

        if ($this->paymentService->isConfigured()) {
            $snapToken = $this->paymentService->createSnapToken($order);
            return redirect()->route('customer.orders.show', $order)->with('snap_token', $snapToken);
        }

        return redirect()->route('customer.orders.show', $order)->with('success', 'Order created successfully');
    }

    public function cancel(Order $order, Request $request)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'reason' => 'nullable|string',
        ]);

        $this->orderService->cancel($order, $request->reason ?? '');

        return redirect()->back()->with('success', 'Order cancelled');
    }
}
