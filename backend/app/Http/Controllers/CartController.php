<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $cart = Cart::with(['items.product.images'])->firstOrCreate(
            ['user_id' => $request->user()->id]
        );

        return $this->success($cart);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::firstOrCreate(
            ['user_id' => $request->user()->id]
        );

        $product = Product::find($validated['product_id']);

        // check stock
        if ($product->stock < $validated['quantity']) {
            return $this->error('Insufficient stock', 400);
        }

        $cartItem = CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'product_id' => $validated['product_id']],
            ['quantity' => $validated['quantity']] // This replaces quantity. Or should it increment? Usually 'Add to cart' increments if exists.
            // Requirement doesn't specify. I'll make it increment if exists, or just set. Standard specific 'Add' usually implies increment.
            // But typical updateOrCreate logic replaces. I'll do manual check.
        );

        // Let's rewrite to increment properly
        $existing = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            $existing->increment('quantity', $validated['quantity']);
            $cartItem = $existing;
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity']
            ]);
        }

        return $this->success($cart->load('items.product'), 'Item added to cart');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        // Ensure user owns this cart item
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return $this->error('Unauthorized', 403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem->update(['quantity' => $validated['quantity']]);

        return $this->success($cartItem->cart->load('items.product'), 'Cart updated');
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return $this->error('Unauthorized', 403);
        }

        $cartItem->delete();

        return $this->success([], 'Item removed from cart');
    }
}
