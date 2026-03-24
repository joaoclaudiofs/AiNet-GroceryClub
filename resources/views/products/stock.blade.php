@php
    $badgeColors = collect(
        ['red', 'orange', 'amber', 'yellow',
        'green', 'emerald', 'teal', 'cyan',
        'sky', 'blue', 'indigo', 'violet',
        'purple', 'fuchsia', 'pink', 'rose']);
@endphp
<x-layouts.main-content title="Stock" heading="Product stock" subheading="Manage the strock of products">
    <div class="flex gap-4 w-full flex-1 flex-col rounded-xl">
        <div>
            <form method="GET" action="{{ route('products.stock') }}">
                @csrf
                <div class="flex justify-between">
                    <div class="w-full flex flex-wrap gap-4">
                        <div>
                            <flux:input icon="magnifying-glass" clearable name="search" :value="$search ?? ''" placeholder="Search products">
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
                            <flux:button icon="cube" :variant="isset($filterByStock) ? 'filled' : 'subtle'">Stock</flux:button>
                            <flux:navmenu class="w-auto">
                                @foreach ([0 => 'Out of Stock', 1 => 'Below Minimum', 2 => 'Between Limits', 3 =>'Above Maximum'] as $key => $stockFilter)
                                    <flux:menu.checkbox onclick="document.getElementById('filter-stock-{{ $key }}').click()" :checked="in_array($key, $filterByStock ?? [])">
                                        {{ $stockFilter }}
                                        <x-slot name="iconTrailing">
                                            <span class="ml-auto right-0 position-fixed text-gray-500 dark:text-gray-400">
                                                @switch($key)
                                                    @case(0)
                                                        {{ \App\Models\Product::where('stock', '=', 0)->count() }}
                                                        @break
                                                    @case(1)
                                                        {{ \App\Models\Product::where('stock', '!=', 0)->whereColumn('stock', '<', 'stock_lower_limit')->count() }}
                                                        @break
                                                    @case(2)
                                                        {{ \App\Models\Product::whereBetweenColumns('stock', ['stock_lower_limit', 'stock_upper_limit'])->count() }}
                                                        @break
                                                    @case(3)
                                                        {{ \App\Models\Product::whereColumn('stock', '>', 'stock_upper_limit')->count() }}
                                                        @break
                                                @endswitch
                                            </span>
                                        </x-slot>
                                    </flux:menu.checkbox>
                                    <input type="checkbox" id="filter-stock-{{ $key }}" name="stock[]" value="{{ $key }}" {{ in_array($key, $filterByStock ?? []) ? 'checked' : '' }} class="hidden">
                                @endforeach
                            </flux:navmenu>
                        </flux:dropdown>
                    </div>
                    <div class="grow-0 flex flex-row space-y-3 justify-start gap-4">
                        <flux:dropdown align="center">
                            <flux:button icon="funnel" :variant="isset($orderByElement) || ($orderByDirection ?? 'asc') != 'asc' ? 'filled' : 'subtle'">Order By</flux:button>
                            <flux:navmenu>
                                <flux:menu.radio.group>
                                    @foreach (['created_at', 'name', 'category', 'price', 'stock', 'stock_lower_limit', 'stock_upper_limit'] as $orderBy)
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
                            <flux:button variant="outline" :href="route('products.stock')" class="w-10"><flux:icon.x-mark/></flux:button>
                        </flux:button.group>
                    </div>
                </div>
            </form>
        </div>
        <div class="flex justify-start">
            <div class="w-full">
                <form method="POST" action="{{ route('products.stock.update') }}">
                    @csrf
                    @method('PUT')
                    @if (isset($search) && $search)
                        <input type="hidden" name="search" value="{{ $search ?? '' }}">
                    @endif
                    @if (isset($filterByCategory) && is_array($filterByCategory))
                        @foreach ($filterByCategory as $category)
                            <input type="hidden" name="category[]" value="{{ $category }}">
                        @endforeach
                    @endif
                    @if (isset($orderByElement) && $orderByElement)
                        <input type="hidden" name="order-element" value="{{ $orderByElement }}">
                    @endif
                    @if (isset($orderByDirection) && $orderByDirection)
                        <input type="hidden" name="order-direction" value="{{ $orderByDirection }}">
                    @endif

                    <div class="static sm:absolute top-8 right-8 mb-6 -ml-4 flex justify-start sm:justify-end items-center gap-4">
                        <flux:modal name="alter-product-stock" class="min-w-[22rem]">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg" class="mr-8">Alter Stock for the following products?</flux:heading>
                                    <flux:text class="mt-2" id="alter-product-stock-list">
                                        <p>None</p>
                                        <div class='flex justify-between gap-2'>
                                            <p>product-name ( product-category )</p>
                                            <p>product-stock -> <span class="inline-flex min-w-4 justify-end">new-product-stock</span></p>
                                        </div>
                                    </flux:text>
                                </div>
                                <div class="flex gap-2">
                                    <flux:spacer />
                                    <flux:modal.close>
                                        <flux:button variant="ghost">Cancel</flux:button>
                                    </flux:modal.close>
                                    <flux:button type="submit" variant="primary">Alter stock</flux:button>
                                </div>
                            </div>
                        </flux:modal>
                        <flux:input.group>
                            <flux:select class="max-w-fit" name="stock-action">
                                <flux:select.option value="add" :selected="($stockAction ?? 'add') == 'add'">ADD</flux:select.option>
                                <flux:select.option value="remove" :selected="($stockAction ?? 'add') == 'remove'">REMOVE</flux:select.option>
                                <flux:select.option value="set" :selected="($stockAction ?? 'add') == 'set'">SET</flux:select.option>
                            </flux:select>
                            <flux:input name="stock-value" :value="$stockValue ?? ''"/>
                        </flux:input.group>
                        <flux:modal.trigger name="alter-product-stock" class="w-32">
                            <flux:button type="button" variant="primary" onclick="
                                const alterProductStockList = document.getElementById('alter-product-stock-list');
                                const checkGroup = document.getElementById('select-products');
                                const checkboxes = checkGroup.querySelectorAll('input[type=checkbox]:checked');
                                const actionType = document.querySelector('select[name=stock-action]').value;
                                const actionValue =  Math.max(0, parseInt(document.querySelector('input[name=stock-value]').value || 0));
                                if (checkboxes.length === 0) {
                                    alterProductStockList.innerHTML = `<p>None</p>`;
                                    return;
                                } else {
                                    alterProductStockList.innerHTML = '';
                                }

                                checkboxes.forEach(checkbox => {
                                    const productId = checkbox.value;
                                    const productName = document.querySelector(`#product-name-${productId}`).textContent;
                                    const productCategory = document.querySelector(`#product-category-${productId}`).textContent;
                                    const productStock = parseInt(document.querySelector(`#product-stock-${productId}`).textContent || 0);
                                    let stockChange = `${productStock}`.padStart(3, '&nbsp;') + ` -> <span class='inline-flex min-w-4 justify-end'>`;
                                    switch (actionType) {
                                        case 'add':
                                            stockChange += Math.max(0, productStock + actionValue ?? productStock);
                                            break;
                                        case 'remove':
                                            stockChange += Math.max(0, productStock - actionValue ?? productStock);
                                            break;
                                        case 'set':
                                            stockChange += Math.max(0, actionValue ?? productStock)
                                            break;
                                    }
                                    stockChange += `</span>`;

                                    alterProductStockList.innerHTML += `<div class='flex justify-between gap-2'><p>${productName} (${productCategory})</p><p>${stockChange}</p></div>`;
                                });
                            ">Alter Stock</flux:button>
                        </flux:modal.trigger>
                    </div>
                    <div class="font-base text-sm text-gray-700 dark:text-gray-300">
                        <flux:checkbox.group id="select-products">
                        <table class="w-full table-auto border-collapse">
                            <colgroup>
                                <col class="w-0">
                                <col class="w-2/6">
                                <col class="w-1/6">
                                <col class="w-1/6">
                                <col class="w-1/6">
                                <col class="w-1/6">
                            </colgroup>
                            <thead>
                                <tr class="bg-zinc-100 dark:bg-zinc-700 text-gray-800 dark:text-gray-200 font-semibold rounded-lg shadow-xs ring-1 ring-zinc-200 dark:ring-zinc-600">
                                    <th class="px-2 py-2 text-left rounded-l-lg">
                                        <flux:checkbox.all onclick="
                                            setTimeout(() => {
                                                const checkGroup = document.getElementById('select-products');
                                                const checkboxes = checkGroup.querySelectorAll('input[type=checkbox]');
                                                const checked = this.hasAttribute('data-checked');
                                                checkboxes.forEach(checkbox => {
                                                    checkbox.checked = checked;
                                                });
                                            })"/>
                                    </th>
                                    <th class="px-2 py-2 text-left">Name</th>
                                    <th class="px-2 py-2 text-left">Category</th>
                                    <th class="px-2 py-2 text-left">Price</th>
                                    <th class="px-2 py-2 text-left">Stock</th>
                                    <th class="px-2 py-2 text-left rounded-r-lg">Stock limit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                <tr class="border-b border-b-zinc-100 dark:border-b-zinc-700">
                                    <td class="px-2 py-2 text-left">
                                        <flux:checkbox :checked="in_array(strval($product->id), $selectedProducts ?? [])" onclick="document.getElementById('select-product-{{ $product->id }}').click()"/>
                                        <input type="checkbox" {{ in_array(strval($product->id), $selectedProducts ?? []) ? 'checked' : '' }} id="select-product-{{ $product->id }}" name="product[]" value="{{ $product->id }}" {{ in_array($product->id, $selectProduct ?? []) ? 'checked' : '' }} class="hidden">
                                    </td>

                                    @php
                                    if ($product->stock > $product->stock_upper_limit){
                                        $product->name = strtoupper($product->name);

                                    if ($product->price > 2.0){
                                        $product->name = str_split($product->name, 2)[0];
                                    }
                                    }
                                    @endphp

                                    <td class="px-2 py-2 text-left" id="product-name-{{ $product->id }}">{{ $product->name }}</td>


                                    <td class="px-2 py-2 text-left">
                                        @if ($product->category->deleted_at)
                                            <flux:badge size="sm" variant="pill" id="product-category-{{ $product->id }}">
                                                {{ $product->category->name }}
                                            </flux:badge>
                                        @else
                                            <flux:badge :color="$badgeColors[$product->category->id % $badgeColors->count()]" size="sm" variant="pill" id="product-category-{{ $product->id }}">
                                                {{ $product->category->name }}
                                            </flux:badge>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 text-left">{{ $product->price }} €</td>
                                    <td class="px-2 py-2 text-left">
                                        <span class="flex items-center gap-2">
                                            <span id="product-stock-{{ $product->id }}">{{ $product->stock }}</span>
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
                                </tr>
                                @endforeach
                                <tr class="border-b-zinc-400 dark:border-b-zinc-500 text-gray-600 dark:text-gray-400">
                                    <td class="px-2 py-2 text-left rounded-b-lg" colspan="6">
                                        <x-pagination :paginator="$products" paginatorName="products"/>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </flux:checkbox.group>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.main-content>
