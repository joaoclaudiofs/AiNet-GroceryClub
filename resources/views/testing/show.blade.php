<x-layouts.main-content title="TEST" heading="TEST VIEW" subheading="VIEW TEST">
    <div class="flex gap-4 w-full flex-1 flex-col rounded-xl">
    <div>
        {{ $order->id }}
    </div>
    <flux:menu.item icon="arrow-left" :href="route('testing.index')">Back</flux:menu.item>
        <div class="flex items -center gap-4 p-4 ">
            <div class="flex-1">
                <h2 class="text-lg font-semibold ">{{ $order->id }}</h2>
                         <h2 class="text-lg font-semibold ">{{ strtolower($order->delivery_address) }}</h2>
                   <h2 class="text-lg font-semibold ">{{ strtoupper($order->status) }}</h2>
                      <h2 class="text-lg font-semibold ">{{ $order->date }}</h2>
                           <h2 class="text-lg font-semibold ">{{ $order->total_items }}</h2>
                      <h2 class="text-lg font-semibold ">{{ 2*($order->total) }}</h2>
            </div>
            <div class="flex-shrink-0">
                <span class="text-green -600 font-semibold">
                    Status: {{ $order->status }}
                </span>
            </div>
        </div>
</x-layouts.main-content>
