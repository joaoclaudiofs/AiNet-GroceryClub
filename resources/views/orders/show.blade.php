<x-layouts.main-content title="Orders" heading="Order Details">
    <div class="max-w-3xl mx-auto p-6 bg-white dark:bg-zinc-800 rounded-xl shadow">
        <div class="mb-6">
            <h1 class="text-2xl font-bold mb-2">Order #{{ $order->id }}</h1>
            <div class="flex flex-wrap gap-4 text-sm text-gray-700 dark:text-gray-300">
                <div>
                    <span class="font-semibold">Date:</span>
                    {{ \Carbon\Carbon::parse($order->date)->format('d/m/Y H:i') }}
                </div>
                <div>
                    <span class="font-semibold">Status:</span>
                    <flux:badge :color="$order->status === 'completed' ? 'green' : ($order->status === 'pending' ? 'amber' : 'gray')" size="sm">
                        {{ ucfirst($order->status) }}
                    </flux:badge>
                </div>
                <div>
                    <span class="font-semibold">NIF:</span>
                    {{ $order->nif ?? '-' }}
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                <span class="font-semibold">Delivery Address:</span>
                {{ $order->delivery_address ?? '-' }}
            </div>
        </div>

        <div class="mb-6">
            <h2 class="font-semibold text-lg mb-2">Order Items</h2>
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="w-full table-auto border-collapse font-base text-sm text-gray-700 dark:text-gray-300">
                    <thead>
                        <tr class="bg-zinc-100 dark:bg-zinc-700 text-gray-800 dark:text-gray-200 font-semibold">
                            <th class="px-4 py-2 text-left rounded-l-lg">Product</th>
                            <th class="px-4 py-2 text-center">Quantity</th>
                            <th class="px-4 py-2 text-right">Unit Price</th>
                            <th class="px-4 py-2 text-right rounded-r-lg">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->item_orders as $item)
                        <tr class="border-b border-zinc-100 dark:border-zinc-700">
                            <td class="px-4 py-2">
                                {{ $item->product->name ?? '' }}
                            </td>
                            <td class="px-4 py-2 text-center">{{ $item->quantity }}</td>
                            <td class="px-4 py-2 text-right">€{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-4 py-2 text-right">€{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-col items-end gap-1 text-base text-gray-700 dark:text-gray-200">
            <div>
                <span class="font-semibold">Shipping Cost:</span>
                <span>€{{ number_format($order->shipping_cost, 2) }}</span>
            </div>
            <div class="text-lg font-bold mt-1">
                <span>Total:</span>
                <span class="ml-2 text-emerald-700 dark:text-emerald-400">€{{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>
</x-layouts.main-content>
