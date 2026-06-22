<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\ShippingService;
use Illuminate\Http\Request;

class ShippingRateController extends Controller
{
    public function __construct(
        private ShippingService $shippingService,
    ) {}

    public function getRates(Request $request)
    {
        $request->validate([
            'destination_postal_code' => 'required|string|max:10',
            'weight' => 'required|integer|min:1',
            'length' => 'required|integer|min:1',
            'width' => 'required|integer|min:1',
            'height' => 'required|integer|min:1',
            'couriers' => 'nullable|array',
            'couriers.*' => 'string',
        ]);

        $rates = $this->shippingService->getRatesByPostalCode(
            $request->destination_postal_code,
            $request->length,
            $request->width,
            $request->height,
            $request->weight,
            $request->couriers ?? [],
        );

        return response()->json(['rates' => $rates]);
    }
}
