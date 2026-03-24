<x-layouts.main-content :title="$product->name"
                        heading="Edit Product"
                        :subheading="$product->name">
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <flux:modal name="delete-product" class="min-w-[22rem]">
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
                <div class="static sm:absolute top-8 right-8 flex flex-wrap justify-start sm:justify-end items-center gap-4">
                    <flux:button.group>
                        <flux:button variant="primary" href="{{ route('products.create', ['product' => $product]) }}" class="w-24">New</flux:button>
                        <flux:button href="{{ route('products.show', ['product' => $product]) }}" class="w-24">View</flux:button>
                        <flux:modal.trigger name="delete-product">
                            <flux:button variant="danger" class="cursor-pointer" class="w-24">Delete</flux:button>
                        </flux:modal.trigger>
                    </flux:button.group>
                </div>

                <form method="POST" action="{{ route('products.update', ['product' => $product]) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mt-6 space-y-4">
                        @include('products.partials.fields', ['mode' => 'edit'])
                    </div>
                    <div class="flex mt-6">
                        <flux:button variant="primary" type="submit"  class="uppercase">Save</flux:button>
                        <flux:button class="uppercase ms-4" href="{{ url()->full() }}">Cancel</flux:button>
                    </div>
                </form>
            </section>
        </div>
    </div>
    <form class="hidden" id="form_to_delete_photo" 
        method="POST"  
        action="{{ route('products.destroy.photo', ['product' => $product]) }}"> 
        @csrf 
        @method('DELETE') 
    </form> 
</x-layouts.main-content>
