<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function index()
    {
        $cartSummary = $this->cartService->getCartSummary(auth()->user());

        return Inertia::render('Customer/Cart/Index', [
            'cart' => $cartSummary,
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'quantity' => 'integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        $this->cartService->addItem(
            auth()->user(),
            $product,
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date),
            $request->quantity ?? 1
        );

        return redirect()->back()->with('success', 'Item added to cart');
    }

    public function update(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $this->cartService->updateItemQuantity(
            auth()->user(),
            $itemId,
            $request->quantity
        );

        return redirect()->back()->with('success', 'Cart updated');
    }

    public function remove($itemId)
    {
        $this->cartService->removeItem(auth()->user(), $itemId);

        return redirect()->back()->with('success', 'Item removed from cart');
    }

    public function clear()
    {
        $this->cartService->clearCart(auth()->user());

        return redirect()->back()->with('success', 'Cart cleared');
    }
}
