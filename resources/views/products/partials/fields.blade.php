@php
    $mode = $mode ?? 'edit';
    $readonly = $mode == 'show';
@endphp
<div class="flex flex-wrap gap-8">
    <div class="grow mt-6 space-y-4">
        <div class="grid sm:grid-cols-2 gap-4">
            <flux:input name="name" label="Name" :value="old('name', $product->name)" :disabled="$readonly" />
            <flux:select name="category" label="Category" :disabled="$readonly" indicator="checkbox" placeholder="Choose category...">
                @foreach (\App\Models\Category::all() as $category)
                    <flux:select.option value="{{ $category->id }}" :selected="old('category_id', $product->category_id) == $category->id" :disabled="$readonly">
                        {{ $category->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="col-span-2 sm:col-span-1">
                <flux:input name="price" label="Price" :value="old('price', $product->price)" :disabled="$readonly"/>
            </div>
            <div class="grid grid-cols-2 gap-4 col-span-2">
                <flux:input name="discount" label="Discount" :value="old('discount', $product->discount)" :disabled="$readonly" />
                <flux:input name="discount_min_qty" label="Discount Min Qty" :value="old('discount_min_qty', $product->discount_min_qty)" :disabled="$readonly" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="col-span-2 sm:col-span-1">
                <flux:input name="stock" label="Stock" :value="old('stock', $product->stock)" :disabled="$readonly" />
            </div>
            <div class="grid grid-cols-2 gap-4 col-span-2">
                <flux:input name="stock_lower_limit" label="Stock Lower Limit" :value="old('stock_lower_limit', $product->stock_lower_limit)" :disabled="$readonly" />
                <flux:input name="stock_upper_limit" label="Stock Upper Limit" :value="old('stock_upper_limit', $product->stock_upper_limit)" :disabled="$readonly" />
            </div>
        </div>

        <flux:textarea name="description" label="Description" :disabled="$readonly" :resize="$readonly ? 'none' : 'vertical'" rows="auto">{{ old('description', $product->description) }}</flux:textarea>
    </div>
    <div class="pb-6 pe-12">
        <x-field.image
            name="photo_file"
            label="Photo"
            width="md"
            :readonly="$readonly"
            deleteTitle="Delete Photo"
            :deleteAllow="($mode == 'edit') && ($product->photo)"
            delete_form="form_to_delete_photo"
            :imageUrl="$product->photoUrl"/>
    </div>
</div>
