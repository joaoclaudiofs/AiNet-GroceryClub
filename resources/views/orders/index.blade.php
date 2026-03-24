<x-layouts.main-content title="Orders" heading="Orders List">

    <div class="max-w-5xl mx-auto space-y-6">
        <form method="GET" action="{{ route('orders.index') }}" class="mb-4">
            @csrf
            <label for="status" class="font-semibold mr-2">Filter by status:</label>
            <select name="status" id="status" onchange="this.form.submit()" class="border p-1 rounded">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="completed" {{ $status === 'canceled' ? 'selected' : '' }}>Canceled</option>
            </select>
        </form>

        @forelse($orders as $order)
            <div class="border rounded p-4 shadow">
                <h2 class="text-lg font-bold">Order #{{ $order->id }}</h2>
                <p><strong>Date:</strong> {{ $order->date }}</p>
                <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                <p><strong>Total:</strong> €{{ number_format($order->total, 2) }}</p>

                @if(Auth::user()->type === 'board' || Auth::user()->type === 'employee')
                    <p><strong>User:</strong> {{ $order->user->name ?? 'Unknown' }}</p>
                @endif

                <ul class="mt-2 list-disc list-inside">
                    @foreach($order->item_orders as $item)
                        <li>
                            {{ $item->product->name ?? 'Deleted product' }} (x{{ $item->quantity }}) - €{{ number_format($item->unit_price, 2) }}
                        </li>
                    @endforeach
                </ul>

                @if ($order->status === 'pending')
                    @if (Auth::user()->type === 'board' || Auth::user()->type === 'employee')
                        <form method="POST" action="{{ route('orders.confirm', $order) }}" class="mt-4">
                            @csrf
                            @method('PUT')
                        <flux:button type="submit" variant="primary" class="w-full">
                                Confirm
                            </flux:button>
                        </form>
                    @endif

                    @if (Auth::user()->type === 'board')
                        <form method="POST" action="{{ route('orders.cancel', $order) }}" class="mt-4">
                            @csrf
                            @method('PUT')
                        <flux:button type="submit" variant="danger" class="w-full">
                                Cancel
                            </flux:button>
                        </form>
                    @endif
                @endif

                <flux:button variant="primary" class="mt-4 w-full" href="{{ route('orders.pdf', $order) }}" target="_blank">
                    PDF
                </flux:button>
            </div>
        @empty
            <p class="text-center text-gray-600">No orders found.</p>
        @endforelse

        <div class="mt-6">
            {{ $orders->appends(['status' => $status])->links() }}
        </div>
    </div>

</x-layouts.main-content>
