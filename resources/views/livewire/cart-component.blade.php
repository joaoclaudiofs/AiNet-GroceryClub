@if (empty($quantities))
    <div class="my-4 p-6">
        <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-300">Yours cart is empty</h2>
    </div>
@else
    <div class="my-4 p-6 w-full">
        <table class="min-w-full text-left border dark:border-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                <tr>
                    <th class="p-2">Product</th>
                    <th class="p-2 text-center">Quantity</th>
                    <th class="p-2 text-right">Unit Price</th>
                    <th class="p-2 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cart as $item)
                    @php
                        $product = \App\Models\Product::find($item['product']['id']);
                        $quantity = $quantities[$product->id] ?? $item['quantity'];
                        $originalPrice = $product->price;
                        $discount = 0;
                        if (!empty($product->discount_min_qty) && $quantity >= $product->discount_min_qty) {
                            $discount = $product->discount;
                        }
                        $unitPrice = max(0, $originalPrice - $discount);
                        $subtotal = $unitPrice * $quantity;
                    @endphp
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td class="text-center">
                            <div class="flex items-center justify-center">
                                <button type="button"
                                        wire:click="decrementQuantity({{ $product->id }})"
                                        class="px-2 py-1 bg-gray-200 rounded-l hover:bg-gray-300">-</button>
                                <input type="number"
                                       min="0"
                                       wire:model.debounce.500ms="quantities.{{ $product->id }}"
                                       class="w-16 px-2 py-1 border-t border-b border-gray-300 text-center" />
                                <button type="button"
                                        wire:click="incrementQuantity({{ $product->id }})"
                                        class="px-2 py-1 bg-gray-200 rounded-r hover:bg-gray-300">+</button>
                            </div>
                        </td>
                        <td class="text-right">
                            @if ($discount > 0)
                                <span class="line-through text-gray-500 mr-2">€{{ number_format($originalPrice, 2) }}</span>
                                <span class="text-green-600 font-semibold">€{{ number_format($unitPrice, 2) }}</span>
                            @else
                                €{{ number_format($unitPrice, 2) }}
                            @endif
                        </td>
                        <td class="text-right">€{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
