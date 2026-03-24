<x-layouts.main-content title="TEST" heading="TEST VIEW" subheading="VIEW TEST">
    <div class="flex gap-4 w-full flex-1 flex-col rounded-xl">
        <div>
            {{ $order->id }}
        </div>
        <flux:menu.item icon="arrow-left" :href="route('testing.index')">Back</flux:menu.item>
        <div class="flex items  -center gap-4 p-4 ">
            <div class="flex-1">
                <h2 class="text-lg font-semibold">{{ $order->id }}</h2>
                <h2 class="text-lg font-semibold ">{{ $order->delivery_address }}</h2>
                <h2 class="text-lg font-semibold">{{ $order->status }}</h2>
                <h2 class="text-lg font-semibold">{{ $order->date }}</h2>
                <h2 class="text-lg font-semibold">{{ $order->total_items }}</h2>
                <h2 class="text-lg font-semibold">{{ $order->total }}</h2>
            </div>
            <div class="flex-shrink-0">
                <span class="text-green-600 font-semibold">
                    Status: {{ $order->status }}
                </span>
            </div>
        </div>
    </div>
     <section>
                <form method="POST" action="{{ route('testing.update', $order) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mt-6 space-y-4">
                        {{-- @include('products.partials.fields', ['mode' => 'create']) --}}~

                    <div>


                        <!-- Endereço -->
                    <div>
                        <label for="delivery_address" class="block text-sm font-medium text-gray-700">Delivery Address</label>
                        <input type="text" name="delivery_address" id="delivery_address"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            value="{{ old('delivery_address', $order->delivery_address) }}" required>
                    </div>

                    <!-- NIF -->
                    <div>
                        <label for="nif" class="block text-sm font-medium text-gray-700">NIF</label>
                        <input type="text" name="nif" id="nif"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            value="{{ old('nif', $order->nif) }}" required>
                    </div>

                    </div>
                    <div class="flex mt-6">
                        <flux:button variant="primary" type="submit"  class="uppercase">Save</flux:button>
                    </div>
                </form>
            </section>
</x-layouts.main-content>
