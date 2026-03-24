<x-layouts.main-content title="TEST" heading="TEST VIEW" subheading="VIEW TEST">
    <div class="flex gap-4 w-full flex-1 flex-col rounded-xl">
        <div>
            {{ $orders->first()->id }}
        </div>
        <flux:menu.item icon="plus" :href="route('testing.create')">Create</flux:menu.item>
        @foreach ($orders as $order)
            <div class="flex items -center gap-4 p-4">
                <div class="flex-1">
                    <h2 class="text-lg font-semibold">{{ $order->id }}</h2>
                    <flux:menu.item icon="eye" :href="route('testing.show', ['order' => $order])">View</flux:menu.item>
                    <flux:menu.item icon="arrow-up" :href="route('testing.edit', ['order' => $order])">Edit</flux:menu.item>
                    <form method="POST" action="{{ route('testing.destroy', $order->id) }}" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600">Cancel Order</button>
                    </form>
                </div>
                <div class="flex-shrink-0">
                    <span class="text-green -600 font-semibold">
                        Status: {{ $order->status }}
                    </span>
                </div>
            </div>
        @endforeach
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</x-layouts.main-content>
