<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Address;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product', 'payment'])
            ->latest()
            ->get();
        return $this->success($orders);
    }

    public function adminIndex()
    {
        $orders = Order::with(['user', 'items', 'payment'])->latest()->paginate(20);
        return $this->success($orders);
    }

    public function show(Order $order, Request $request)
    {
        if ($order->user_id !== $request->user()->id && !$request->user()->hasRole(['admin', 'super_admin'])) {
            return $this->error('Unauthorized', 403);
        }
        return $this->success($order->load(['items.product', 'payment', 'address']));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'address_id' => 'required|exists:addresses,id', // Assuming user selects existing address OR sends address data. Simplified for now.
            // If address data sent inline, need different logic. Let's assume user creates address first or sends ID.
            // Given "Addresses" is a required table, I should probably allow creating one.
            // But let's stick to reference for simplicity in this step, or inline.
        ]);

        // Actually, better to allow inline address creation or ID.
        // Let's assume ID for now to keep it clean.

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return $this->error('Cart is empty', 400);
        }

        // Calculate total
        $total = 0;
        foreach ($cart->items as $item) {
            $total += $item->product->price * $item->quantity;
        }

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $validated['address_id'],
                'total_amount' => $total,
                'status' => 'pending',
                'payment_status' => 'pending'
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price
                ]);
            }

            // Razorpay Order Creation
            $api = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));
            $razorpayOrder = $api->order->create([
                'receipt' => 'order_' . $order->id,
                'amount' => $total * 100, // Amount in paise
                'currency' => 'INR'
            ]);

            $order->razorpay_order_id = $razorpayOrder->id;
            $order->save();

            // Clear Cart
            $cart->items()->delete();

            DB::commit();

            return $this->success([
                'order' => $order->load('items'),
                'razorpay_order_id' => $razorpayOrder->id,
                'amount' => $total * 100,
                'currency' => 'INR',
                'key' => env('RAZORPAY_KEY_ID')
            ], 'Order created initiated', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Order creation failed: ' . $e->getMessage(), 500);
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order->update(['status' => $validated['status']]);
        return $this->success($order, 'Order status updated');
    }
}
