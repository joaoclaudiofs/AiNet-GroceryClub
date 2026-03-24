<x-layouts.main-content title="Categories"
                        heading="List of categories"
                        subheading="Manage the categories of the store">
    <div class="flex gap-4 w-full flex-1 flex-col rounded-xl">
        <div>
            <form method="GET" action="{{ route('categories.index') }}">
                @csrf
                <div class="flex justify-between">
                    <div class="w-full flex flex-wrap gap-4">
                        <div>
                            <flux:input icon="magnifying-glass" clearable name="search-name" :value="$searchByName ?? ''" placeholder="Search categories"/>
                        </div>
                    </div>
                    <div class="grow-0 flex flex-row space-y-3 justify-start gap-4">
                        <flux:dropdown align="center">
                            <flux:button icon="funnel" :variant="isset($orderBy) || ($direction ?? 'asc') != 'asc' ? 'filled' : 'subtle'">Order By</flux:button>
                            <flux:navmenu>
                                <flux:menu.radio.group>
                                    @foreach (['name', 'created_at'] as $orderOption)
                                        <flux:menu.radio onclick="document.getElementById('order-by-{{ $orderOption }}').click()" :checked="($orderBy ?? 'name') == '{{ $orderOption }}'">
                                            {{ ucwords(str_replace('_', ' ', $orderOption)) }}
                                        </flux:menu.radio>
                                        <input type="radio" id="order-by-{{ $orderOption }}" name="order-by" value="{{ $orderOption }}" {{ ($orderBy ?? 'name') == $orderOption ? 'checked' : '' }} class="hidden">
                                    @endforeach
                                </flux:menu.radio.group>
                                <flux:radio.group variant="segmented" size="sm" class="mt-2">
                                    <flux:radio onclick="document.getElementById('direction-asc').click()" :checked="($direction ?? 'asc') == 'asc'">
                                        <flux:tooltip content="Ascending" position="bottom">
                                            <flux:icon icon="bars-arrow-down" />
                                        </flux:tooltip>
                                    </flux:radio>
                                    <input type="radio" id="direction-asc" name="direction" value="asc" {{ ($direction ?? 'asc') == 'asc' ? 'checked' : '' }} class="hidden">
                                    <flux:radio onclick="document.getElementById('direction-desc').click()" :checked="($direction ?? 'asc') == 'desc'">
                                        <flux:tooltip content="Descending" position="bottom">
                                            <flux:icon icon="bars-arrow-up" />
                                        </flux:tooltip>
                                    </flux:radio>
                                    <input type="radio" id="direction-desc" name="direction" value="desc" {{ ($direction ?? 'asc') == 'desc' ? 'checked' : '' }} class="hidden">
                                </flux:radio.group>
                            </flux:navmenu>
                        </flux:dropdown>
                        <flux:button.group>
                            <flux:button variant="primary" type="submit" class="cursor-pointer w-28">Filter</flux:button>
                            <flux:button variant="outline" :href="route('categories.index')" class="w-10"><flux:icon.x-mark/></flux:button>
                        </flux:button.group>
                    </div>
                </div>
            </form>
        </div>
        <div class="flex justify-start">
            <div class="w-full">
                <div class="static sm:absolute top-8 right-8 mb-6 flex flex-wrap justify-start sm:justify-end items-center gap-4">
                    <flux:button.group>
                        <flux:button variant="primary" icon="plus" :href="route('categories.create')">Create a new category</flux:button>
                    </flux:button.group>
                </div>
                <div class="font-base text-sm text-gray-700 dark:text-gray-300">
                    <table class="w-full table-auto border-collapse">
                        <colgroup>
                            <col class="w-2/5">
                            <col class="w-1/5">
                            <col class="w-1/5">
                            <col class="w-1/5">
                            <col class="w-0">
                        </colgroup>
                        <thead>
                                <tr class="bg-zinc-100 dark:bg-zinc-700 text-gray-800 dark:text-gray-200 font-semibold rounded-lg shadow-xs ring-1 ring-zinc-200 dark:ring-zinc-600">
                                <th class="px-2 py-2 text-left rounded-l-lg">Name</th>
                                <th class="px-2 py-2 text-left">Description</th>
                                <th class="px-2 py-2 text-left">Products</th>
                                <th class="px-2 py-2 text-left">Created at</th>
                                <th class="px-2 py-2 text-left rounded-r-lg"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr class="border-b border-b-zinc-100 dark:border-b-zinc-700">
                                    <td class="px-2 py-2 text-left">
                                        @if ($category->deleted_at)
                                            <s class="text-gray-500 dark:text-gray-400">{{ $category->name }}</s>
                                        @else
                                            {{ $category->name }}
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 text-left">{{ $category->description ?? '-' }}

                                        <img src="{{ $category->ImageUrl }}"></img>
                                    </td>
                                    <td class="px-2 py-2 text-left">{{ $category->products_count ?? $category->products->count() }}</td>
                                    <td class="px-2 py-2 text-left">{{ $category->created_at->format('Y-m-d') }}</td>
                                    <td class="px-1 py-1 text-left">
                                        <flux:modal :name="'delete-category-'.$category->id" class="min-w-[22rem]">
                                            <div class="space-y-6">
                                                <div>
                                                    <flux:heading size="lg">Delete Category {{ $category->name }}?</flux:heading>
                                                    <flux:text class="mt-2">
                                                        <p>You're about to delete this category.</p>
                                                        <p>This action cannot be reversed.</p>
                                                    </flux:text>
                                                </div>
                                                <div class="flex gap-2">
                                                    <flux:spacer />
                                                    <flux:modal.close>
                                                        <flux:button variant="ghost">Cancel</flux:button>
                                                    </flux:modal.close>
                                                    <form method="POST" id="form-delete-category-{{ $category->id }}" action="{{ route('categories.destroy', ['category' => $category]) }}" class="flex items-center">
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
                                                <flux:menu.item icon="eye" :href="route('categories.show', ['category' => $category])">View</flux:menu.item>
                                                <flux:menu.item icon="pencil-square" :href="route('categories.edit', ['category' => $category])">Edit</flux:menu.item>
                                                {{-- @if($category->name == 'Fruits') --}}
                                                <flux:modal.trigger name="delete-category-{{ $category->id }}">
                                                 <flux:menu.item icon="trash" variant="danger" class="cursor-pointer">Delete</flux:menu.item>
                                                </flux:modal.trigger>
                                                {{-- @endif --}}
                                            </flux:menu>
                                        </flux:dropdown>
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="border-b-zinc-400 dark:border-b-zinc-500 text-gray-600 dark:text-gray-400">
                                    <td class="px-2 py-2 text-left rounded-b-lg" colspan="5">
                                        <x-pagination :paginator="$categories" paginatorName="categories"/>
                                    </td>
                                </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.main-content>
