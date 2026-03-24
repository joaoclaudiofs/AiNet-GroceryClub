<x-layouts.main-content title="TEST" heading="TEST VIEW" subheading="VIEW TEST">
    <div class="flex gap-4 w-full flex-1 flex-col rounded-xl">
         <table class="min-w-full divide-y divide-gray-200 border">
                <thead class=">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity Changed</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Custom</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Created At</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Updated At</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="">
                    @foreach($adjusts as $adjust)
                        <tr>
                            <td class="px-4 py-2">
                                @if($adjust->product && $adjust->product->photo_url)
                                    <img src="{{ $adjust->product->photo_url }}" alt="{{ $adjust->product->name }}" class="h-12 w-12 object-cover rounded" />
                                @else
                                    <span class="text-gray-400">No image</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">{{ $adjust->id }}</td>
                            <td class="px-4 py-2">{{ $adjust->product->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ $adjust->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ $adjust->quantity_changed }}</td>
                            <td class="px-4 py-2">{{ $adjust->custom }}</td>
                            <td class="px-4 py-2">{{ $adjust->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2">{{ $adjust->updated_at->format('d/m/Y H:i') }}</td>
                            <td>  <flux:menu.item icon="eye" :href="route('stockAdjust.show', ['adjust' => $adjust])">View</flux:menu.item>  <form method="POST" action="{{ route('stockAdjust.destroy', $adjust->id) }}" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600">Remove adjust</button>
                    </form></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        <div class="mt-4">
            {{ $adjusts->links() }}
        </div>
    </div>
</x-layouts.main-content>
