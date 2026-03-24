@php
    $badgeColors = collect(
        ['red', 'orange', 'amber', 'yellow',
        'green', 'emerald', 'teal', 'cyan',
        'sky', 'blue', 'indigo', 'violet',
        'purple', 'fuchsia', 'pink', 'rose']);
@endphp
<x-layouts.main-content title="Products" heading="List of products" subheading="Manage the products of the store">
    <div class="flex gap-4 w-full flex-1 flex-col rounded-xl">
        <div>
            <form method="GET" action="{{ route('products.index') }}">
                @csrf
                <div class="flex justify-between">
                    <div class="w-full flex flex-wrap gap-4">
                        <div>
                            <flux:input icon="magnifying-glass" clearable name="search-name" :value="$searchByName ?? ''" placeholder="Search products">
                                <x-slot name="iconTrailing">
                                    <flux:button square id="filled-regex-true" size="sm" variant="filled" class="-mr-2 font-mono {{ isset($searchWithRegex) && $searchWithRegex ? '' : 'hidden!' }}" onclick="document.getElementById('filter-regex').click()">.*</flux:button>
                                    <flux:button square id="filled-regex-false" size="sm" variant="subtle" class="-mr-2 font-mono {{ isset($searchWithRegex) && $searchWithRegex ? 'hidden!' : '' }}" onclick="document.getElementById('filter-regex').click()">.*</flux:button>
                                    <input type="checkbox" id="filter-regex" name="search-with-regex" value="1" {{ isset($searchWithRegex) && $searchWithRegex ? 'checked' : '' }} class="hidden" onclick="document.getElementById('filled-regex-true').classList.toggle('hidden!'); document.getElementById('filled-regex-false').classList.toggle('hidden!');"/>
                                </x-slot>
                            </flux:input>
                        </div>    
                        <flux:dropdown align="center">
                            <flux:button icon="tag" :variant="isset($filterByCategory) ? 'filled' : 'subtle'">Category</flux:button>
                            <flux:navmenu class="w-auto">
                                @foreach (\App\Models\Category::withTrashed()->get()->sortBy('name') as $category)
                                    @if (!$category->deleted_at || $category->products->count() > 0)
                                        <flux:menu.checkbox onclick="document.getElementById('filter-category-{{ $category->id }}').click()" :checked="in_array($category->id, $filterByCategory ?? [])">
                                            @if ($category->deleted_at)
                                                <s class="text-gray-500 dark:text-gray-400">
                                                    {{ $category->name }}
                                                </s>
                                            @else
                                                {{ $category->name }}
                                            @endif
                                            <x-slot name="iconTrailing">
                                                <span class="ml-auto right-0 position-fixed text-gray-500 dark:text-gray-400">{{ $category->products->count() }}</span>
                                            </x-slot>
                                        </flux:menu.checkbox>
                                        <input type="checkbox" id="filter-category-{{ $category->id }}" name="category[]" value="{{ $category->id }}" {{ in_array($category->id, $filterByCategory ?? []) ? 'checked' : '' }} class="hidden">
                                    @endif
                                @endforeach
                            </flux:navmenu>
                        </flux:dropdown>
                        <flux:dropdown align="center">
                            <flux:button icon="currency-euro" :variant="isset($filterByPrice) && (isset($filterByPrice['min']) || isset($filterByPrice['max']))
                                                                     || isset($filterByDiscount) && (isset($filterByDiscount['min']) || isset($filterByDiscount['max']) || isset($filterByDiscount['no-null']))
                                                                     || isset($filterByDiscountMinQty) && (isset($filterByDiscountMinQty['min']) || isset($filterByDiscountMinQty['max']) || isset($filterByDiscountMinQty['no-null']))
                                                                     ? 'filled' : 'subtle'">Price</flux:button>
                            <flux:navmenu class="w-52">
                                <flux:menu.group heading="Price">
                                    <flux:menu.item>
                                        <flux:input.group>
                                            <flux:input size="sm" name="price[min]" :value="$filterByPrice['min'] ?? ''" type="number" min="0" step="0.01" placeholder="min"/>
                                            <flux:input size="sm" name="price[max]" :value="$filterByPrice['max'] ?? ''" type="number" min="0" step="0.01" placeholder="max"/>
                                        </flux:input.group>
                                    </flux:menu.item>
                                </flux:menu.group>
                                <flux:menu.group heading="Discount">
                                    <flux:menu.item>
                                        <flux:input.group>
                                            <flux:input size="sm" name="discount[min]" :value="$filterByDiscount['min'] ?? ''" type="number" min="0" step="0.01" placeholder="min"/>
                                            <flux:input size="sm" name="discount[max]" :value="$filterByDiscount['max'] ?? ''" type="number" min="0" step="0.01" placeholder="max"/>
                                        </flux:input.group>
                                    </flux:menu.item>
                                    <flux:menu.item>
                                        <flux:checkbox label="Exclude empty values" name="discount[no-null]" :checked="$filterByDiscount['no-null'] ?? false" value="1" class="mt-2"/>
                                    </flux:menu.item>
                                </flux:menu.group>
                                <flux:menu.group heading="Discount Min Qty">
                                    <flux:menu.item>
                                        <flux:input.group>
                                            <flux:input size="sm" name="discount_min_qty[min]" :value="$filterByDiscountMinQty['min'] ?? ''" type="number" min="0" step="0.01" placeholder="min"/>
                                            <flux:input size="sm" name="discount_min_qty[max]" :value="$filterByDiscountMinQty['max'] ?? ''" type="number" min="0" step="0.01" placeholder="max"/>
                                        </flux:input.group>
                                    </flux:menu.item>
                                    <flux:menu.item>
                                        <flux:checkbox label="Exclude empty values" name="discount_min_qty[no-null]" :checked="$filterByDiscountMinQty['no-null'] ?? false" value="1" class="mt-2"/>
                                    </flux:menu.item>
                                </flux:menu.group>
                            </flux:navmenu>
                        </flux:dropdown>
                        <flux:dropdown align="center">
                            <flux:button icon="cube" :variant="isset($filterByStock) && (isset($filterByStock['min']) || isset($filterByStock['max']))
                                                            || isset($filterByStockLowerLimit) && (isset($filterByStockLowerLimit['min']) || isset($filterByStockLowerLimit['max']))
                                                            || isset($filterByStockUpperLimit) && (isset($filterByStockUpperLimit['min']) || isset($filterByStockUpperLimit['max']))
                                                            ? 'filled' : 'subtle'">Stock</flux:button>
                            <flux:navmenu class="w-52">
                                <flux:menu.group heading="Stock">
                                    <flux:menu.item>
                                        <flux:input.group>
                                            <flux:input size="sm" name="stock[min]" :value="$filterByStock['min'] ?? ''" type="number" min="0" step="1" placeholder="min"/>
                                            <flux:input size="sm" name="stock[max]" :value="$filterByStock['max'] ?? ''" type="number" min="0" step="1" placeholder="max"/>
                                        </flux:input.group>
                                    </flux:menu.item>
                                </flux:menu.group>
                                <flux:menu.group heading="Stock Lower Limit">
                                    <flux:menu.item>
                                        <flux:input.group>
                                            <flux:input size="sm" name="stock_lower_limit[min]" :value="$filterByStockLowerLimit['min'] ?? ''" type="number" min="0" step="1" placeholder="min"/>
                                            <flux:input size="sm" name="stock_lower_limit[max]" :value="$filterByStockLowerLimit['max'] ?? ''" type="number" min="0" step="1" placeholder="max"/>
                                        </flux:input.group>
                                    </flux:menu.item>
                                </flux:menu.group>
                                <flux:menu.group heading="Stock Upper Limit">
                                    <flux:menu.item>
                                        <flux:input.group>
                                            <flux:input size="sm" name="stock_upper_limit[min]" :value="$filterByStockUpperLimit['min'] ?? ''" type="number" min="0" step="1" placeholder="min"/>
                                            <flux:input size="sm" name="stock_upper_limit[max]" :value="$filterByStockUpperLimit['max'] ?? ''" type="number" min="0" step="1" placeholder="max"/>
                                        </flux:input.group>
                                    </flux:menu.item>
                                </flux:menu.group>
                            </flux:navmenu>
                        </flux:dropdown>
                    </div>
                    <div class="grow-0 flex flex-row space-y-3 justify-start gap-4">
                        <flux:dropdown align="center">
                            <flux:button icon="funnel" :variant="isset($orderByElement) || ($orderByDirection ?? 'asc') != 'asc' ? 'filled' : 'subtle'">Order By</flux:button>
                            <flux:navmenu>
                                <flux:menu.radio.group>
                                    @foreach (['created_at', 'name', 'category', 'price', 'discount', 'discount_min_qty', 'stock', 'stock_lower_limit', 'stock_upper_limit'] as $orderBy)
                                        <flux:menu.radio onclick="document.getElementById('order-element-{{ $orderBy }}').click()" :checked="($orderByElement ?? 'created_at') == $orderBy">
                                            {{ ucwords(str_replace('_', ' ', $orderBy)) }}
                                        </flux:menu.radio>
                                        <input type="radio" id="order-element-{{ $orderBy }}" name="order-element" value="{{ $orderBy != 'created_at' ? $orderBy: '' }}" {{ ($orderByElement ?? 'created_at') == $orderBy ? 'checked' : '' }} class="hidden">
                                    @endforeach
                                </flux:menu.radio.group>
                                <flux:radio.group variant="segmented" size="sm" class="mt-2">
                                    <flux:radio onclick="document.getElementById('order-direction-asc').click()" :checked="($orderByDirection ?? 'asc') == 'asc'">
                                        <flux:tooltip content="Ascending" position="bottom">
                                            <flux:icon icon="bars-arrow-down" />
                                        </flux:tooltip>
                                    </flux:radio>
                                    <input type="radio" id="order-direction-asc" name="order-direction" value="asc" {{ ($orderByDirection ?? 'asc') == 'asc' ? 'checked' : '' }} class="hidden">
                                    <flux:radio onclick="document.getElementById('order-direction-desc').click()" :checked="($orderByDirection ?? 'asc') == 'desc'">
                                        <flux:tooltip content="Descending" position="bottom">
                                            <flux:icon icon="bars-arrow-up" />
                                        </flux:tooltip>
                                    </flux:radio>
                                    <input type="radio" id="order-direction-desc" name="order-direction" value="desc" {{ ($orderByDirection ?? 'asc') == 'desc' ? 'checked' : '' }} class="hidden">
                                </flux:radio.group>
                            </flux:navmenu>
                        </flux:dropdown>
                        <flux:button.group>
                            <flux:button variant="primary" type="submit" class="cursor-pointer w-28">Filter</flux:button>
                            <flux:button variant="outline" :href="route('products.index')" class="w-10"><flux:icon.x-mark/></flux:button>
                        </flux:button.group>
                    </div>
                </div>
            </form>
        </div>
        <div class="flex justify-start max-w-full">
            <div class="w-full">
                <div class="static sm:absolute top-8 right-8 mb-6 flex flex-wrap justify-start sm:justify-end items-center gap-4">
                    <flux:button.group>
                        <flux:button variant="primary" icon="plus" :href="route('products.create')">Create a new product</flux:button>
                    </flux:button.group>
                </div>
                <div class="font-base text-sm text-gray-700 dark:text-gray-300">
                    <table class="w-full table-auto border-collapse">
                        <colgroup>
                            <col class="w-2/8">
                            <col class="w-1/8">
                            <col class="w-1/8">
                            <col class="w-1/8">
                            <col class="w-1/8">
                            <col class="w-1/8">
                            <col class="w-1/8">
                            <col class="w-0">
                        </colgroup>
                        <thead>
                            <tr class="bg-zinc-100 dark:bg-zinc-700 text-gray-800 dark:text-gray-200 font-semibold rounded-lg shadow-xs ring-1 ring-zinc-200 dark:ring-zinc-600">
                                <th class="px-2 py-2 text-left rounded-l-lg">Name</th>
                                <th class="px-2 py-2 text-left">Category</th>
                                <th class="px-2 py-2 text-left">Price</th>
                                <th class="px-2 py-1 text-left">Discount</th>
                                <th class="px-2 py-1 text-left">Discount Min Qty</th>
                                <th class="px-2 py-2 text-left">Stock</th>
                                <th class="px-2 py-2 text-left">Stock limit</th>
                                <th class="px-2 py-2 text-left rounded-r-lg"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                            <tr class="border-b border-b-zinc-100 dark:border-b-zinc-700">
                                <td class="px-2 py-2 text-left">{{ $product->name }}</td>
                                <td class="px-2 py-2 text-left">
                                    <a href="{{ route('categories.show', ['category' => $product->category]) }}">
                                        @if ($product->category->deleted_at)
                                            <flux:badge size="sm" icon="tag">
                                                {{ $product->category->name }}
                                            </flux:badge>
                                        @else
                                            <flux:badge :color="$badgeColors[$product->category->id % $badgeColors->count()]" size="sm" icon="tag">
                                                {{ $product->category->name }}
                                            </flux:badge>
                                        @endif
                                    </a>
                                </td>
                                <td class="px-2 py-2 text-left">{{ $product->price }} €</td>
                                <td class="px-2 py-2 text-left">
                                    @if ($product->discount)
                                        {{ $product->discount }} €
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-left">
                                    @if ($product->discount_min_qty)
                                        {{ $product->discount_min_qty }}
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-left">
                                    <span class="flex items-center gap-2">
                                        {{ $product->stock }}
                                        @if ($product->stock < $product->stock_lower_limit)
                                            <flux:tooltip content="Stock is below the lower limit">
                                                <flux:icon variant="micro" name="chevron-down" class="text-red-500 dark:text-red-400"/>
                                            </flux:tooltip>
                                        @elseif ($product->stock > $product->stock_upper_limit)
                                            <flux:tooltip content="Stock is above the upper limit">
                                                <flux:icon variant="micro" name="chevron-up" class="text-amber-500 dark:text-amber-400"/>
                                            </flux:tooltip>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-left">
                                    <span class="flex items-center gap-4">
                                        <flux:tooltip content="Lower Limit">
                                            <span class="flex items-center">
                                                {{ $product->stock_lower_limit }}
                                                <flux:icon variant="micro" name="arrow-long-down"/>
                                            </span>
                                        </flux:tooltip>
                                        <flux:tooltip content="Upper Limit">
                                            <span class="flex items-center">
                                                {{ $product->stock_upper_limit }}
                                                <flux:icon variant="micro" name="arrow-long-up"/>
                                            </span>
                                        </flux:tooltip>
                                    </span>
                                </td>
                                <td class="px-1 py-1 text-left">
                                    <flux:modal :name="'delete-product-'.$product->id" class="min-w-[22rem]">
                                        <div class="space-y-6">
                                            <div>
                                                <flux:heading size="lg">Delete Product {{ $product->name }}?</flux:heading>
                                                <flux:text class="mt-2">
                                                    <p>You're about to delete this product.</p>
                                                    <p>This action cannot be reversed.</p>
                                                </flux:text>
                                            </div>
                                            <div class="flex gap-2">
                                                <flux:spacer />
                                                <flux:modal.close>
                                                    <flux:button variant="ghost">Cancel</flux:button>
                                                </flux:modal.close>
                                                <form method="POST" id="form-delete-product-{{ $product->id }}" action="{{ route('products.destroy', ['product' => $product]) }}" class="flex items-center">
                                                    @csrf
                                                    @method('DELETE')
                                                    <flux:button type="submit" variant="danger">Delete</flux:button>
                                                </form>
                                            </div>
                                        </div>
                                    </flux:modal>
                                    <flux:dropdown class="w-48" align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"/>
                                        <flux:menu>
                                            <flux:menu.item icon="eye" :href="route('products.show', ['product' => $product])">View</flux:menu.item>
                                            <flux:menu.item icon="pencil-square" :href="route('products.edit', ['product' => $product])">Edit</flux:menu.item>
                                            <flux:modal.trigger name="delete-product-{{ $product->id }}">
                                                <flux:menu.item icon="trash" variant="danger" class="cursor-pointer">Delete</flux:menu.item>
                                            </flux:modal.trigger>
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                            @endforeach
                            <tr class="border-b-zinc-400 dark:border-b-zinc-500 text-gray-600 dark:text-gray-400">
                                <td class="px-2 py-2 text-left rounded-b-lg" colspan="8">
                                    <x-pagination :paginator="$products" paginatorName="products"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.main-content>
