<x-layouts.main-content title="TEST" heading="TEST VIEW" subheading="VIEW TEST">
    <div class="flex gap-4 w-full flex-1 flex-col rounded-xl">
     <section>
                <form method="POST" action="{{ route('testing.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mt-6 space-y-4">
                        {{-- @include('products.partials.fields', ['mode' => 'create']) --}}~

                           <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" name="name" id="name"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ Auth::user()->name }}" disabled>
                            </div>

                            <!-- Endereço -->
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700">Delivery Address</label>
                                <input type="text" name="address" id="address"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('address', Auth::user()->address) }}" required>
                            </div>

                            <!-- NIF -->
                            <div>
                                <label for="nif" class="block text-sm font-medium text-gray-700">NIF</label>
                                <input type="text" name="nif" id="nif"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('nif', Auth::user()->nif) }}" required>
                            </div>
                    </div>
                    <div class="flex mt-6">
                        <flux:button variant="primary" type="submit"  class="uppercase">Save</flux:button>
                    </div>
                </form>
            </section>
</x-layouts.main-content>
