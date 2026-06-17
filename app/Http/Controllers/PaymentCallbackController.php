<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentProcessingException;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function handle(Request $request)
    {
        $notification = $request->all();

        Log::info('Payment notification received', ['data' => $notification]);

        try {
            $payment = $this->paymentService->handleNotification($notification);

            return response()->json([
                'success' => true,
                'message' => 'Payment notification processed successfully',
            ]);
        } catch (PaymentProcessingException $e) {
            Log::error('Payment processing error: ' . $e->getMessage(), [
                'notification' => $notification,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected payment error: ' . $e->getMessage(), [
                'notification' => $notification,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }
}
