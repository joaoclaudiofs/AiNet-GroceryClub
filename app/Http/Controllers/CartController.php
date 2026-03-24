<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Item_order;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CartConfirmationFormRequest;
use App\Mail\OrderMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Settings_shipping_costs;

class CartController extends Controller
{

    public function show()
    {
        $cart = session('cart', []);
        $shipping = $this->calculateShipping($cart);
        $total = $this->calculateTotal($cart, $shipping);

        return view('cart.show', compact('cart', 'shipping', 'total'));
    }


    public function add(Request $request, Product $product)
    {


        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart = session('cart', []);


        if($product->category->name === 'Fruits' || $product->category->name === 'Vegetables'){
            $quantity *= 2;

            // $product = Product::where('name', 'Banana')->first();
        }




        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'product' => $product->toArray(),
                'quantity' => $quantity,
            ];
        }

        session(['cart' => $cart]);

        return back()->with('alert-type', 'success')
                     ->with('alert-msg', "Product \"{$product->name}\" added to cart.");
    }


    public function update(Request $request, Product $product)
    {
        $quantity = max(0, (int) $request->input('quantity'));
        $cart = session('cart', []);

        if (isset($cart[$product->id])) {
            if ($quantity === 0) {
                unset($cart[$product->id]);
            } else {
                $cart[$product->id]['quantity'] = $quantity;
            }
        }

        session(['cart' => $cart]);

        return back();
    }

    public function remove(Request $request, Product $product)
    {
        $cart = session('cart', []);
        if (isset($cart[$product->id])) {
            unset($cart[$product->id]);
            session(['cart' => $cart]);
              return back()->with('alert-type', 'success')
                     ->with('alert-msg', "Product \"{$product->name}\" removed from cart.");
        }
        return back()->with('alert-type', 'warning')
                     ->with('alert-msg', "Product \"{$product->name}\" not found at cart.");
    }

    public function clear(Request $request)
    {
        $request->session()->forget('cart');

        return back()->with('alert-type', 'success')
                     ->with('alert-msg', 'Shopping cart cleared.');
    }

    public function ajaxUpdate(Request $request)
    {
        $cart = session('cart', []);
        $id = $request->input('product_id');
        $qty = max(0, (int)$request->input('quantity'));

        if (isset($cart[$id])) {
            if ($qty > 0) {
                $cart[$id]['quantity'] = $qty;
            } else {
                unset($cart[$id]);
            }
            session(['cart' => $cart]);
        }

        $cartCount = array_sum(array_column($cart, 'quantity'));
        return response()->json(['cartCount' => $cartCount]);
    }

    public function confirm(CartConfirmationFormRequest $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('alert-type', 'warning')->with('alert-msg', 'Please login to confirm your purchase.');
        }

        $cart = session('cart', []);
        $quantities = $request->input('quantities', []);
        foreach ($cart as $id => &$item) {
            if (isset($quantities[$id])) {
                $item['quantity'] = max(0, (int)$quantities[$id]);
            }
        }
        unset($item);
        $cart = array_filter($cart, fn($item) => $item['quantity'] > 0);
        session(['cart' => $cart]);

        if (empty($cart)) {
            return back()->with('alert-type', 'danger')->with('alert-msg', 'Your cart is empty.');
        }

        $shipping = $this->calculateShipping($cart);
        $total = $this->calculateTotal($cart, $shipping);
        $card = $user->card;

        if (!$card || $card->balance < $total) {
            return back()->with('alert-type', 'danger')->with('alert-msg', 'Insufficient balance in your virtual card.');
        }#

        $order = Order::create([
            'member_id' => $user->id,
            'delivery_address' => $request->input('address', $user->address),
            'nif' => $request->input('nif', $user->nif),
            'shipping_cost' => $shipping,
            'total' => $total,
            'date' => now()->toDateString(),
            'status' => 'pending',
            'total_items' => count($cart),
        ]);
        $order->load('user');
        Mail::to($order->user->email)->send(new OrderMail($order));

        $delayedProducts = [];

     foreach ($cart as $item) {
        $product = Product::find($item['product']['id']);
        if (!$product) {
            continue;
        }
        $quantity = $item['quantity'];
        $unitPrice = $this->getDiscountedPrice($product, $quantity);

        Item_order::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount' => $product->discount ?? 0,
            'subtotal' => $this->getSubtotal($product, $quantity),
        ]);

        if ($product->stock < $quantity) {
            $delayedProducts[] = $product->name;
        }
        }

        $operation = $card->operations()->create([
            'type' => 'debit',
            'value' => $total,
            'date' => now()->format('Y-m-d'),
            'payment_type' => $user->default_payment_type,
            'payment_reference' => $user->default_payment_reference,
        ]);

        $card->balance -= $total;
        $card->save();

        session()->forget('cart');

        $message = 'Order confirmed and is being prepared.';
        if (!empty($delayedProducts)) {
            $message .= ' Some products may be delayed due to insufficient stock: ' . implode(', ', $delayedProducts) . '.';
        }

        return redirect()->route('orders.show', ['order' => $order])
                        ->with('alert-type', 'success')
                        ->with('alert-msg', $message);
    }

    private function calculateShipping(array $cart): float
    {
        $total = 0;
        foreach ($cart as $item) {
            $product = \App\Models\Product::find($item['product']['id']);
            $quantity = $item['quantity'];

            $total += $this->getSubtotal($product, $quantity);
        }


        $shippingSetting = Settings_shipping_costs::where('min_value_threshold', '<=', $total)
            ->where('max_value_threshold', '>=', $total)
            ->orderBy('min_value_threshold', 'desc')
            ->first();

        return $shippingSetting ? (float) $shippingSetting->shipping_cost : 0.00;
    }

    private function calculateTotal(array $cart, float $shipping): float
    {
        $total = 0;
        foreach ($cart as $item) {
            $product = Product::find($item['product']['id']);
            $quantity = $item['quantity'];
            $total += $this->getSubtotal($product, $quantity);
        }
        return $total + $shipping;
    }

    private function getDiscountedPrice(Product $product, int $quantity): float
    {
        if ($product->discount && $quantity >= $product->discount_min_qty) {
            return round($product->price * (1 - $product->discount / 100), 2);
        }
        return round($product->price, 2);
    }

    private function getSubtotal(Product $product, int $quantity): float
    {
        return round($this->getDiscountedPrice($product, $quantity) * $quantity, 2);
    }
}
