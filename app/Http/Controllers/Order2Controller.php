<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockFormRequest;
use App\Models\Order;
use App\Models\Stock_adjustment;
use App\Models\View_product_stock_logs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\ProductFormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\CartConfirmationFormRequest;
use Illuminate\Support\Facades\Auth;
use Termwind\Components\Raw;

class Order2Controller extends Controller
{

    public function index(Request $request): View
    {
        $orders = Order::where('status', 'pending')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('testing.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        return view('testing.show')->with('order', $order);
    }

    public function create(): View
    {
        return view('testing.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
         $order = Order::create([
            'member_id' => $user->id,
            'delivery_address' => $request->input('address', $user->address),
            'nif' => $request->input('nif', $user->nif),
            'shipping_cost' => 10,
            'total' => 10,
            'date' => now()->toDateString(),
            'status' => 'pending',
            'total_items' => 10
        ]);

        return Redirect::route('testing.index')->with('success', 'Order created successfully.');
    }

    public function edit(Order $order): View
    {
        return view('testing.edit')->with('order', $order);
    }

    public function update(Order $order, Request $request): RedirectResponse
    {
        $order->fill($request->only([
            'delivery_address',
            'nif',
        ]));
        $order->save();

        return Redirect::route('testing.index')->with('success', 'Order updated successfully.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        //$order->delete();
        $order->update([
            'status' => 'canceled',
        ]);
        return Redirect::route('testing.index')->with('success', 'Order deleted successfully.');
    }

}
