<x-layouts.main-content :title="$product->name"
                        :subheading="$product->category->name ?? 'Uncategorized'">
    <section class="mt-8 max-w-5xl mx-auto px-4">
        <div class="flex flex-col md:flex-row gap-10">

            <div class="md:w-2/3 flex flex-col justify-between order-1">
                <div>
                    <h1 class="text-3xl font-extrabold mb-2">{{ $product->name }}</h1>
                    <p class="text-gray-600 mb-4">{{ $product->category->name ?? 'Uncategorized' }}</p>

                    <p class=" font-bold text-3xl">€{{ number_format($product->price, 2) }}</p>

                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-2">Description</h2>
                        <p>{{ $product->description }}</p>
                    </div>
                </div>

                <form action="{{ route('cart.add', $product) }}" method="POST" class="flex items-center space-x-4">
                    @csrf
                    <label for="quantity" class="font-medium text-gray-700">Quantity:</label>
                    <input
                        id="quantity"
                        name="quantity"
                        type="number"
                        value="1"
                        min="1"
                        class="w-20 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600"
                    />

                    <flux:button type="submit" variant="primary">
                         Add to Cart
                    </flux:button>
                </form>
            </div>

            <div class="md:w-1/3 flex justify-center items-center order-2">
                <x-field.image
                    name="photo_file"
                    label="Photo"
                    width="full"
                    :readonly="true"
                    :imageUrl="$product->photoUrl"
                    class="rounded-lg shadow-lg max-h-[400px] object-contain"
                />
            </div>
        </div>
    </section>

            @foreach ($PC as $product)
                <div class="md:w-1/3 mt-4">
                    {{ $product->name }}
                </div>
            @endforeach
</x-layouts.main-content>
