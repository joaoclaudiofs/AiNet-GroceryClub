<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\OrderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function show(Order $order)
    {
        $order->load('item_orders.product', 'operations');

        return view('orders.show', compact('order'));
    }


    public function showUserOrders()
    {
        $user = Auth::user();
        $orders = Order::with('item_orders.product')
            ->where('member_id', $user->id)
            ->orderByDesc('date')
            ->paginate(20);

        return view('orders.showUser', compact('orders'));
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->query('status', 'all');

        $query = Order::with('user', 'item_orders.product')
                    ->orderByDesc('date');

        if ($user->type !== 'board' && $user->type !== 'employee') {
            $query->where('member_id', $user->id);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20);

        return view('orders.index', compact('orders', 'status'));
    }

    public function confirm(Order $order)
    {
        foreach ($order->item_orders as $item) {
            if ($item->quantity > $item->product->stock) {
                return redirect()->back()
                    ->with('alert-type', 'error')
                    ->with('alert-msg', "Not enough {$item->product->name} in stock!");
            }
        }
    
        $order->status = 'completed';
        $order->save();

        Mail::to($order->user->email)->send(new OrderMail($order));

        return redirect()->back()
            ->with('alert-type', 'success')
            ->with('alert-msg', 'Order completed successfully!');
    }


    public function cancel(Order $order) {

        DB::transaction(function () use ($order) {
            $order->status = 'canceled';
            $order->save();
            
            $card = $order->user->card;
            $card->operations()->create([
                'type' => 'credit',
                'value' => $order->total,
                'date' => now()->format('Y-m-d'),
                'credit_type' => 'order_cancellation',
                'order_id' => $order->id,
            ]);

            $card->balance += $order->total;
            $card->save();
        });

        Mail::to($order->user->email)->send(new OrderMail($order));

        return redirect()->back()
            ->with('alert-type', 'success')
            ->with('alert-msg', 'Order cancelled successfully!');
    }

    public function pdf(Order $order) {
        $pdf = Pdf::loadView('orders.pdf', compact('order'));
        return $pdf->download($order->pdf_receipt ?? "receipt_order_{$order->id}");
    }

}
