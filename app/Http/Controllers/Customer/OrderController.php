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
        $query = Order::with(['items.product', 'payment'])
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

        $order->load(['items.product', 'payment', 'reviews']);

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
            'customer_name' => 'required|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'return_date' => 'required|date',
            'refund_bank_name' => 'required|string|max:255',
            'refund_bank_account' => 'required|string|max:50',
            'refund_bank_holder' => 'required|string|max:255',
            'shipping_address' => 'nullable|string',
            'city' => 'nullable|string',
            'province' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'district' => 'nullable|string',
            'suburb' => 'nullable|string',
            'shipping_courier' => 'nullable|string',
            'shipping_service' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);

        $order = $this->orderService->createFromCart(
            auth()->user(),
            $request->only([
                'address', 'phone', 'notes',
                'customer_name', 'instagram', 'return_date',
                'refund_bank_name', 'refund_bank_account', 'refund_bank_holder',
                'shipping_address', 'city', 'province', 'postal_code',
                'district', 'suburb',
                'shipping_courier', 'shipping_service', 'shipping_cost',
            ]),
        );

        if ($this->paymentService->isConfigured()) {
            $snapToken = $this->paymentService->createSnapToken($order);
            return redirect()->route('customer.orders.show', $order)->with('snap_token', $snapToken);
        }

        return redirect()->route('customer.orders.show', $order)->with('success', 'Pesanan berhasil dibuat');
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
