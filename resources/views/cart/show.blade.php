@php
    $shippingRules = \App\Models\Settings_shipping_costs::orderBy('min_value_threshold')->get(['min_value_threshold', 'max_value_threshold', 'shipping_cost']);
@endphp

<x-layouts.main-content title="Cart"
                        heading="Shopping Cart"
                        subheading="Products you intend to purchase">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex justify-start">
            @if (empty($cart))
                <div class="my-4 p-6">
                    <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-300">Your cart is empty</h2>
                </div>
            @else
                <div class="my-4 p-6 w-full bg-white dark:bg-zinc-800 rounded-xl shadow">
                    <table class="min-w-full text-left border dark:border-gray-700 rounded-xl overflow-hidden" id="cart-table">
                        <thead class="bg-zinc-100 dark:bg-zinc-700 text-gray-800 dark:text-gray-200 font-semibold">
                            <tr>
                                <th class="p-3 text-left rounded-l-lg">Product</th>
                                <th class="p-3 text-center">Quantity</th>
                                <th class="p-3 text-right">Unit Price</th>
                                <th class="p-3 text-right">Subtotal</th>
                                <th class="p-3 text-center rounded-r-lg">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cart as $item)
                                @php
                                    $product = \App\Models\Product::find($item['product']['id']);
                                    $quantity = $item['quantity'];
                                    $originalPrice = $product->price;
                                    $discount = 0;
                                    if (!empty($product->discount_min_qty) && $quantity >= $product->discount_min_qty) {
                                        $discount = $product->discount;
                                    }
                                    $unitPrice = max(0, $originalPrice - $discount);
                                    $subtotal = $unitPrice * $quantity;
                                @endphp
                                <tr data-product-id="{{ $product->id }}"
                                    data-original-price="{{ $originalPrice }}"
                                    data-discount="{{ $product->discount ?? 0 }}"
                                    data-discount-min-qty="{{ $product->discount_min_qty ?? 0 }}"
                                    class="border-b border-zinc-100 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                                    <td class="p-3 font-semibold flex items-center gap-2">
                                        {{ $product->name }}
                                        @if ($discount > 0)
                                            <flux:badge color="green" size="xs" icon="tag">Discount</flux:badge>
                                        @endif
                                    </td>
                                    <td class="text-center p-3">
                                        <input type="number"
                                               name="quantity"
                                               value="{{ $quantity }}"
                                               min="0"
                                               class="w-16 px-2 py-1 border rounded text-center cart-qty-input"
                                               data-product-id="{{ $product->id }}"
                                        />
                                    </td>
                                    <td class="text-right p-3">
                                        <span class="unit-price">
                                            @if ($discount > 0)
                                                <span class="line-through text-gray-500 mr-2">€{{ number_format($originalPrice, 2) }}</span>
                                                <span class="text-green-600 font-semibold">€{{ number_format($unitPrice, 2) }}</span>
                                            @else
                                                €{{ number_format($unitPrice, 2) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-right p-3">
                                        <span class="subtotal font-mono">€{{ number_format($subtotal, 2) }}</span>
                                    </td>
                                    <td class="text-center p-3">
                                        <form action="{{ route('cart.remove', $product->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-8 text-right text-gray-800 dark:text-gray-300 space-y-1">
                        <p>Shipping: <strong id="shipping-value">€{{ number_format($shipping, 2) }}</strong></p>
                        <p class="text-lg font-semibold">Total: <strong id="total-value" class="text-emerald-700 dark:text-emerald-400">€{{ number_format($total, 2) }}</strong></p>
                    </div>

                    <div class="mt-12">
                        <h3 class="mb-4 text-xl font-semibold">Checkout</h3>
                        <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                         <form action="{{ route('cart.confirm') }}" method="post" class="flex flex-col space-y-4 w-full max-w-lg">
                            @csrf
                            @foreach ($cart as $item)
                                <input type="hidden" name="quantities[{{ $item['product']['id'] }}]" value="{{ $item['quantity'] }}" class="cart-qty-hidden" data-product-id="{{ $item['product']['id'] }}">
                            @endforeach
                            <flux:input name="nif" label="NIF" value="{{ old('nif', Auth::user()->nif ?? '') }}" />
                            <flux:input name="address" label="Delivery Address" value="{{ old('address', Auth::user()->default_delivery_address ?? '') }}" />
                            <flux:button variant="primary" type="submit">Confirm Purchase</flux:button>
                        </form>

                            <form action="{{ route('cart.clear') }}" method="post">
                                @csrf
                                @method('DELETE')
                                <flux:button variant="danger" type="submit" class="mt-[1.7rem]">Clear Cart</flux:button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <script>
    var shippingRules = @json($shippingRules);

    function getShippingCost(total) {
        for (const rule of shippingRules) {
            if (total >= parseFloat(rule.min_value_threshold) && total <= parseFloat(rule.max_value_threshold)) {
                return parseFloat(rule.shipping_cost);
            }
        }
        return 0;
    }

    document.querySelectorAll('.cart-qty-input').forEach(input => {
        input.addEventListener('input', function () {
            const productId = this.dataset.productId;
            let qty = parseInt(this.value) || 0;
            const hiddenInput = document.querySelector(`input.cart-qty-hidden[data-product-id="${productId}"]`);


            fetch('{{ route('cart.ajaxUpdate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ product_id: productId, quantity: qty })
            })
            .then(response => response.json())
            .then(data => {
                const badgeParent = document.getElementById('cart-badge');
                const badgeElement = badgeParent.getElementsByTagName('span')[0];
                if (badgeElement) badgeElement.textContent = data.cartCount;

                if (qty <= 0) {
                    const tr = this.closest('tr');
                    if (tr) tr.remove();
                    if (hiddenInput) hiddenInput.remove();
                    if (data.cartCount === 0) {
                        location.reload();
                        return;
                    }
                } else {

                    if (hiddenInput) {
                        hiddenInput.value = qty;
                    }

                    const tr = this.closest('tr');
                    const originalPrice = parseFloat(tr.dataset.originalPrice);
                    const discount = parseFloat(tr.dataset.discount);
                    const discountMinQty = parseInt(tr.dataset.discountMinQty);
                    let unitPrice = originalPrice;
                    if (discountMinQty > 0 && qty >= discountMinQty) {
                        unitPrice = Math.max(0, originalPrice - discount);
                    }

                    const unitPriceCell = tr.querySelector('.unit-price');
                    if (discountMinQty > 0 && qty >= discountMinQty && discount > 0) {
                        unitPriceCell.innerHTML = `<span class="line-through text-gray-500 mr-2">€${originalPrice.toFixed(2)}</span>
                            <span class="text-green-600 font-semibold">€${unitPrice.toFixed(2)}</span>`;
                    } else {
                        unitPriceCell.innerHTML = `€${unitPrice.toFixed(2)}`;
                    }

                    const subtotal = unitPrice * qty;
                    tr.querySelector('.subtotal').textContent = `€${subtotal.toFixed(2)}`;
                }


                let total = 0;
                document.querySelectorAll('tr[data-product-id]').forEach(row => {
                    const rowQty = parseInt(row.querySelector('.cart-qty-input').value) || 1;
                    const rowOriginal = parseFloat(row.dataset.originalPrice);
                    const rowDiscount = parseFloat(row.dataset.discount);
                    const rowMinQty = parseInt(row.dataset.discountMinQty);
                    let rowUnit = rowOriginal;
                    if (rowMinQty > 0 && rowQty >= rowMinQty) {
                        rowUnit = Math.max(0, rowOriginal - rowDiscount);
                    }
                    total += rowUnit * rowQty;
                });

                const shipping = getShippingCost(total);
                document.getElementById('shipping-value').textContent = `€${shipping.toFixed(2)}`;
                document.getElementById('total-value').textContent = `€${(total + shipping).toFixed(2)}`;
            });
        });
    });
    </script>
</x-layouts.main-content>
