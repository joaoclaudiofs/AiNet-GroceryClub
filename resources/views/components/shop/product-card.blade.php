<a href="{{ route('shop.product', ['product' => $product->id, 'name' => str_replace(' ', '-', strtolower($product->name))]) }}"
    class="relative flex overflow-hidden flex-col bg-zinc-100 dark:bg-zinc-700 font-semibold rounded-lg shadow-xs ring-1 ring-zinc-200 dark:ring-zinc-600 relative cursor-pointer hover:shadow-lg hover:ring-zinc-300 dark:hover:ring-zinc-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <img src="{{ $product->photo_url }}" alt="{{ $product->name }}" class="w-full object-cover aspect-square" />
    <div class="p-4 flex flex-col justify-between h-full">
        <h3>{{ $product->name }}</h3>
        <div class="w-full flex justify-between flex-wrap">
            <flux:text variant="subtle" class="flex items-center gap-1">
                <flux:icon icon="tag" variant="micro" title="Category"/>
                {{ $product->category->name ?? 'Uncategorized' }}
            </flux:text>

            <flux:text>{{ number_format($product->price, 2) }} €

                @if ($product->category->name == 'Bakery')
                    <span class="text-xs">Na compra de 3 unidades o preço será {{ $product->price/2 }}</span>
                @endif

            </flux:text>


        </div>
        <div class="absolute top-0 right-2 flex flex-col items-end justify-end gap-2 mt-2">
            @if ($product->discount_min_qty != null)
                <flux:tooltip content="This product is eligible for a discount when purchasing {{ $product->discount_min_qty }} or more">
                    <flux:badge color="yellow" variant="solid" size="sm" icon="star">Discounted</flux:badge>
                </flux:tooltip>
            @endif
            @if ($product->stock <= $product->stock_lower_limit)
                <flux:tooltip content="Delivery for this product may take longer">
                    <flux:badge color="red" variant="solid" size="sm" icon="chevron-double-down">Low stock</flux:badge>
                </flux:tooltip>
            @endif

            <div class="flex items-center justify-between mt-2">
                @if(isset(session('cart')[$product->id]))
                    <form action="{{ route('cart.remove', $product) }}" method="POST" class="flex items-center space-x-4">
                        @csrf
                        @method('DELETE')
                        <flux:button type="submit" variant="primary" size="sm" class="w-full">
                            Remove from Cart
                        </flux:button>
                    </form>
                @else
                    {{-- <form action="{{ route('cart.add', $product) }}" method="POST" class="flex items-center space-x-4">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <flux:button type="submit" variant="primary" size="sm" class="w-full">
                            Add to Cart
                        </flux:button>
                    </form> --}}
                @endif

                  <form action="{{ route('products.destroy.photo', $product) }}" method="POST" class="flex items-center space-x-4">
                        @csrf
                        @method('DELETE')
                        <flux:button type="submit" variant="primary" size="sm" class="w-full">
                            Remove photo
                        </flux:button>
                    </form>
            </div>
        </div>

    </div>
</a>
