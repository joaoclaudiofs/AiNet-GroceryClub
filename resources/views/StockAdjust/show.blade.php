<x-layouts.main-content title="TEST" heading="TEST VIEW" subheading="VIEW TEST">
    <div class="flex gap-4 w-full flex-1 flex-col rounded-xl">
    <div>
        {{ $adjust->id }}
    </div>
    <flux:menu.item icon="arrow-left" :href="route('stockAdjust.index')">Back</flux:menu.item>
        <div class="flex items -center gap-4 p-4 ">
            <div class="flex-1">
                <td class="px-1 py-1 text-left">
                <img class="w-8 h-8 object-cover rounded" src="{{ $adjust->product->PhotoUrl ?? asset('images/no-image.png') }}" alt="Image">
                </td>
                <h2 class="text-lg font-semibold ">{{ $adjust->id }}</h2>
                <h2 class="text-lg font-semibold ">{{ $adjust->product->name }}</h2>
                <h2 class="text-lg font-semibold ">{{ $adjust->user->name }}</h2>
                <h2 class="text-lg font-semibold ">{{ $adjust->created_at }}</h2>
                   <h2 class="text-lg font-semibold ">{{ $adjust->updated_at }}</h2>
                <h2 class="text-lg font-semibold ">{{ $adjust->quantity_changed }}</h2>
            </div>
            <div class="flex-shrink-0">
                <span class="text-green -600 font-semibold">
                    Status: {{ $adjust->custom }}
                </span>
            </div>
        </div>
</x-layouts.main-content>
