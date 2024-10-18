<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class PaymentController extends Controller
{
    use ApiResponse;

    public function verifyPayment(Request $request)
    {
        $validated = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string'
        ]);

        $api = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));

        try {
            $attributes = [
                'razorpay_order_id' => $validated['razorpay_order_id'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature']
            ];

            $api->utility->verifyPaymentSignature($attributes);

            // Signature verified successfully
            $order = Order::where('razorpay_order_id', $validated['razorpay_order_id'])->firstOrFail();

            if ($order->payment_status === 'paid') {
                return $this->success($order, 'Payment already verified');
            }

            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing' // Move to processing
            ]);

            Payment::create([
                'order_id' => $order->id,
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
                'amount' => $order->total_amount,
                'currency' => 'INR',
                'status' => 'captured', // Assuming auto-capture
                'method' => 'razorpay'
            ]);

            return $this->success($order, 'Payment verified successfully');
        } catch (SignatureVerificationError $e) {
            return $this->error('Payment verification failed: ' . $e->getMessage(), 400);
        }
    }
}
