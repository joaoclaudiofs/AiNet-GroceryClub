<x-layouts.main-content title="Shop" heading="Product Catalog" subheading="Browse our products">
    <div class="flex gap-4 w-full flex-1 flex-col rounded-xl">
        <div>
            <form method="GET" action="{{ route('shop.index') }}">
                @csrf
                <div class="flex justify-between">
                    <div class="w-full flex flex-wrap gap-4">
                        <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4">
                            <flux:input icon="magnifying-glass" clearable name="search" :value="$search ?? ''" placeholder="Search products" />
                        </div>
                        <flux:dropdown align="center">
                            <flux:button icon="tag" :variant="isset($filterByCategory) ? 'filled' : 'subtle'">Category</flux:button>
                            <flux:navmenu class="w-auto">
                                @foreach (\App\Models\Category::get()->sortBy('name') as $category)
                                    @if ($category->products->count() > 0)
                                        <flux:menu.checkbox onclick="document.getElementById('filter-category-{{ $category->id }}').click()" :checked="in_array($category->id, $filterByCategory ?? [])">
                                           {{ $category->name }}
                                        </flux:menu.checkbox>
                                        <input type="checkbox" id="filter-category-{{ $category->id }}" name="category[]" value="{{ $category->id }}" {{ in_array($category->id, $filterByCategory ?? []) ? 'checked' : '' }} class="hidden">
                                    @endif
                                @endforeach
                            </flux:navmenu>
                        </flux:dropdown>
                        <flux:dropdown align="center">
                            <flux:button icon="currency-euro" :variant="isset($filterByPrice) && (isset($filterByPrice['min']) || isset($filterByPrice['max']) || isset($filterByPrice['discount'])) ? 'filled' : 'subtle'">Price</flux:button>
                            <flux:navmenu class="w-52">
                                <flux:menu.item>
                                    <flux:input size="sm" name="price[min]" :value="$filterByPrice['min'] ?? ''" type="number" min="0" step="0.01" placeholder="min price"/>
                                </flux:menu.item>
                                <flux:menu.item>
                                    <flux:input size="sm" name="price[max]" :value="$filterByPrice['max'] ?? ''" type="number" min="0" step="0.01" placeholder="max price"/>
                                </flux:menu.item>
                                <flux:menu.item>
                                    <flux:checkbox label="Only discounted" name="price[discount]" :checked="$filterByPrice['discount'] ?? false" value="1" class="mt-2"/>
                                </flux:menu.item>
                            </flux:navmenu>
                        </flux:dropdown>
                    </div>
                    <div class="grow-0 flex flex-row space-y-3 justify-start gap-4">
                        <flux:dropdown align="center">
                            <flux:button icon="funnel" :variant="isset($orderByElement) || ($orderByDirection ?? 'desc') != 'desc' ? 'filled' : 'subtle'">Order By</flux:button>
                            <flux:navmenu>
                                <flux:menu.radio.group>
                                    @foreach (['created_at', 'name', 'category', 'price'] as $orderBy)
                                        <flux:menu.radio onclick="document.getElementById('order-element-{{ $orderBy }}').click()" :checked="($orderByElement ?? 'created_at') == $orderBy">
                                            {{ ucwords(str_replace('_', ' ', $orderBy)) }}
                                        </flux:menu.radio>
                                        <input type="radio" id="order-element-{{ $orderBy }}" name="order-element" value="{{ $orderBy != 'created_at' ? $orderBy: '' }}" {{ ($orderByElement ?? 'created_at') == $orderBy ? 'checked' : '' }} class="hidden">
                                    @endforeach
                                </flux:menu.radio.group>
                                <flux:radio.group variant="segmented" size="sm" class="mt-2">
                                    <flux:radio onclick="document.getElementById('order-direction-asc').click()" :checked="($orderByDirection ?? 'desc') == 'asc'">
                                        <flux:tooltip content="Ascending" position="bottom">
                                            <flux:icon icon="bars-arrow-down" />
                                        </flux:tooltip>
                                    </flux:radio>
                                    <input type="radio" id="order-direction-asc" name="order-direction" value="asc" {{ ($orderByDirection ?? 'desc') == 'asc' ? 'checked' : '' }} class="hidden">
                                    <flux:radio onclick="document.getElementById('order-direction-desc').click()" :checked="($orderByDirection ?? 'desc') == 'desc'">
                                        <flux:tooltip content="Descending" position="bottom">
                                            <flux:icon icon="bars-arrow-up" />
                                        </flux:tooltip>
                                    </flux:radio>
                                    <input type="radio" id="order-direction-desc" name="order-direction" value="desc" {{ ($orderByDirection ?? 'desc') == 'desc' ? 'checked' : '' }} class="hidden">
                                </flux:radio.group>
                            </flux:navmenu>
                        </flux:dropdown>
                        <flux:button.group>
                            <flux:button variant="primary" type="submit" class="cursor-pointer w-28">Filter</flux:button>
                            <flux:button variant="outline" :href="route('shop.index')" class="w-10"><flux:icon.x-mark/></flux:button>
                        </flux:button.group>
                    </div>
                </div>
            </form>
        </div>
        <div class="flex flex-col gap-4">
            <div class="flex w-full gap-8 grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                @foreach ($products as $product)
                    <x-shop.product-card :product="$product" />
                @endforeach
            </div>
            <div class="text-gray-600 dark:text-gray-400">
                <x-pagination :paginator="$products" paginatorName="products"/>
            </div>
        </div>
    </div>
</x-layouts.main-content>
