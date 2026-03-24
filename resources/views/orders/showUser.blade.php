@php
    $badgeColors = collect([
        'red', 'orange', 'amber', 'yellow',
        'green', 'emerald', 'teal', 'cyan',
        'sky', 'blue', 'indigo', 'violet',
        'purple', 'fuchsia', 'pink', 'rose'
    ]);
@endphp

<x-layouts.main-content title="My Orders" heading="My Orders" subheading="All your orders in the club">
    <div class="flex gap-4 w-full flex-1 flex-col rounded-xl">
        <div class="font-base text-sm text-gray-700 dark:text-gray-300">
            <table class="w-full table-auto border-collapse">
                <colgroup>
                    <col class="w-1/8">
                    <col class="w-2/8">
                    <col class="w-2/8">
                    <col class="w-1/8">
                    <col class="w-1/8">
                </colgroup>
                <thead>
                    <tr class="bg-zinc-100 dark:bg-zinc-700 text-gray-800 dark:text-gray-200 font-semibold rounded-lg shadow-xs ring-1 ring-zinc-200 dark:ring-zinc-600">
                        <th class="px-2 py-2 text-left rounded-l-lg">Order #</th>
                        <th class="px-2 py-2 text-left">Date</th>
                        <th class="px-2 py-2 text-left">Status</th>
                        <th class="px-2 py-2 text-left">Total</th>
                        <th class="px-2 py-2 text-left rounded-r-lg">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr class="border-b border-b-zinc-100 dark:border-b-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                            <td class="px-2 py-2 text-left font-semibold">#{{ $order->id }}</td>
                            <td class="px-2 py-2 text-left">{{ \Carbon\Carbon::parse($order->date)->format('Y-m-d H:i') }}</td>
                            <td class="px-2 py-2 text-left">
                                <flux:badge :color="$badgeColors[$order->id % $badgeColors->count()]" size="sm">
                                  @php
                                        if ($order->status === 'pending') {
                                            $statusText = ucfirst($order->status);
                                        } elseif ($order->status === 'completed') {
                                            $statusText = strtoupper($order->status);
                                        } elseif ($order->status === 'canceled') {
                                            $statusText = strtolower($order->status);
                                        } else {
                                            $statusText = $order->status;
                                        }
                                @endphp
                                    {{ $statusText }}
                                </flux:badge>
                            </td>
                            <td class="px-2 py-2 text-left">
                                <span class="font-mono">€{{ number_format($order->total, 2) }}</span>
                            </td>
                            <td class="px-2 py-2 text-left">
                                <a href="{{ route('orders.show', $order) }}" class="text-blue-600 underline hover:text-blue-800 transition">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 dark:text-gray-400 py-8">
                                You have no orders.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-layouts.main-content>
