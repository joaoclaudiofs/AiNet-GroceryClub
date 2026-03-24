<?php

use App\Models\Settings_shipping_costs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Traits\UserPhotoFileStorage;

new class extends Component {

    
    public array $costs = [
        [0.00, 0.00]
    ];

    /**
     * Mount the component.
     */
    public function mount(): void
    {   
        $shippingCosts = Settings_shipping_costs::get(['min_value_threshold', 'shipping_cost']);
        if ($shippingCosts->isNotEmpty()) {
            $this->costs = $shippingCosts->map(function ($cost) {
                return [floatval($cost->min_value_threshold), floatval($cost->shipping_cost)];
            })->toArray();
        }
    }

    public function addShippingCost(): void
    {
        $this->costs[] = [0.00, 0.00];
    }

    public function removeShippingCost(int $index): void
    {
        if (isset($this->costs[$index])) {
            unset($this->costs[$index]);
            $this->costs = array_values($this->costs); // Re-index the array
        }
    }

    /**
     * Update the shipping costs for the currently authenticated user.
     */
    public function updateShippingCosts(): void
    {
        $validated = $this->validate([
            'costs' => ['required', 'array'],
            'costs.*.0' => ['required', 'numeric', 'min:0.00', 'max:9999999.99'],
            'costs.*.1' => ['required', 'numeric', 'min:0.00', 'max:9999999.99'],
        ]);


        for ($i = 0; $i < count($validated['costs']) - 1; $i++) {
            if ($validated['costs'][$i][0] >= $validated['costs'][$i + 1][0]) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'costs' => __('The start of the price range must be less than or equal to the next price range start.')
                ]);
            }
        }

        DB::beginTransaction();
        DB::transaction(function () use ($validated) {

            Settings_shipping_costs::truncate();


            for ($i = 0; $i < count($validated['costs']); $i++) {
                $costData = $validated['costs'][$i];
                $nextCostThreshold = $i + 1 < count($validated['costs']) ? $validated['costs'][$i + 1][0] : 9999999.99;

                Settings_shipping_costs::create([
                    'min_value_threshold' => $costData[0],
                    'max_value_threshold' => $nextCostThreshold,
                    'shipping_cost' => $costData[1]
                ]);
            }
        });

        $this->dispatch('shipping-updated', costs: $validated['costs']);
    }
}; ?>


<section class="w-full">
    @include('partials.business-heading')

    <x-business.layout :heading="__('Shipping')" :subheading="__('Configure the shipping settings for your business.')">
        <div>
            <form wire:submit.prevent="updateShippingCosts" class="my-5 w-full space-y-6">
                @csrf
                <flux:label>Shipping costs</flux:label>

                <div class="flex flex-col my-2 gap-2">
                    <div class="items-center relative flex">
                        <div class="w-136">
                            <flux:text>Order Price Range</flux:text>
                        </div>
                        <div class="w-full">
                            <flux:text>Shipping Cost</flux:text>
                        </div>
                    </div>
                    <script>
                        function priceRangeStartInputHandler(event) {
                            console.log(event, event.target.parentElement)

                            const id = parseInt(event.target.parentElement.parentElement.getAttribute('data-price-range-id'));
                            if (id == 0) {
                                return; // No need to sync the first input
                            }
                            const value = parseFloat(event.target.value);
                            const syncInput = document.getElementById('price-range-end-' + (id - 1));
                            if (syncInput) {
                                if (parseFloat(syncInput.value) != value) {
                                    syncInput.value = value;
                                }
                            }
                        }
                        function priceRangeEndInputHandler(event) {

                            const id = parseInt(event.target.getAttribute('data-price-range-id'));
                            if (event.target.value == '∞') {
                                return; // No need to sync the last input
                            }
                            const value = parseFloat(event.target.value);
                            const syncInput = document.getElementById('price-range-start-' + (id + 1));
                            if (syncInput) {
                                if (parseFloat(syncInput.value) != value) {
                                    syncInput.value = value;
                                }
                            }
                        }

                        function priceRangeStartChangeHandler(event) {

                            const id = parseInt(event.target.getAttribute('data-price-range-id'));
                            if (id == 0) {
                                return; // No need to sync the first input
                            }
                            const value = parseFloat(event.target.value);

                            const nextInput = document.getElementById('price-range-start-' + (id + 1));
                            if (nextInput && nextInput.value != '∞' && parseFloat(nextInput.value) <= value) {
                                nextInput.value = parseFloat((value + 0.01).toFixed(2));
                                nextInput.dispatchEvent(new Event('input', { bubbles: true }));
                                nextInput.dispatchEvent(new Event('change', { bubbles: true }));
                            }

                            const previousInput = document.getElementById('price-range-start-' + (id - 1));
                            if (previousInput && previousInput.getAttribute('data-price-range-id') != 0 && parseFloat(previousInput.value) >= value) {
                                previousInput.value = parseFloat((value - 0.01).toFixed(2));
                                previousInput.dispatchEvent(new Event('input', { bubbles: true }));
                                previousInput.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                        function priceRangeEndChangeHandler(event) {

                            const id = parseInt(event.target.getAttribute('data-price-range-id'));
                            if (event.target.value == '∞') {
                                return; // No need to sync the last input
                            }
                            const syncInput = document.getElementById('price-range-start-' + (id + 1));
                            if (syncInput) {
                                syncInput.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                    </script>
                    <div id="price-range-list" class="flex flex-col gap-2">  
                        @for ($i = 0; $i < count($costs); $i++)

                            <div key="price-range-{{ $i }}" id="price-range-container-{{ $i }}" data-price-range-id="{{ $i }}" class="items-center relative flex rounded-lg ">
                                <flux:input required wire:model="costs.{{ $i }}.0" :id="'price-range-start-'.$i" :data-price-range-id="$i" :type="$i == 0 ? 'text' : 'number'" class="max-w-24" value="{{ $costs[$i][0] ?? '' }}" min="0" max="9999999.99" step="0.01" :disabled="$i == 0" />
                                <flux:icon name="ellipsis-horizontal" variant="mini" class="mx-2 text-zinc-300 dark:text-zinc-500" />
                                <flux:input :id="'price-range-end-'.$i" :data-price-range-id="$i" :type="$i + 1 < count($costs) ? 'number' : 'text'" class="max-w-24" class:input="before:content-['∞'] before:w-full" value="{{ isset($costs[$i + 1]) ? $costs[$i + 1][0] : '∞' }}" min="0" max="9999999.99" step="0.01" :disabled="$i == count($costs) - 1" />
                                <flux:icon name="arrow-right" variant="mini" class="mx-2 text-zinc-300 dark:text-zinc-500" />
                                <flux:input required wire:model="costs.{{ $i }}.1" :id="'price-range-value-'.$i" type="number" class="w-full" value="{{ $costs[$i][1] ?? 0 }}" min="0" max="9999999.99" step="0.01" />
                                @if ($i > 0)
                                    <flux:button :id="'price-range-remove-'.$i" icon="x-mark" variant="subtle" size="xs" class="aspect-square ml-2 mr-1" wire:click.prevent="removeShippingCost({{ $i }})"/>
                                @else
                                    <div class="w-22"></div>
                                @endif
                                <script>
                                    var startInput = document.getElementById('price-range-start-{{ $i }}');
                                    startInput.addEventListener('input', priceRangeStartInputHandler);
                                    startInput.addEventListener('change', priceRangeStartChangeHandler);
                                    var endInput = document.getElementById('price-range-end-{{ $i }}');
                                    endInput.addEventListener('input', priceRangeEndInputHandler);
                                    endInput.addEventListener('change', priceRangeEndChangeHandler);
                                </script>
                            </div>
                        @endfor
                    </div>
                    <script>
                        const priceRangeList = document.getElementById('price-range-list');
                        const observer = new MutationObserver((mutationsList) => {
                            mutationsList.forEach((mutation) => {
                                if (mutation.type !== 'childList') return;
                                mutation.addedNodes.forEach((node) => {
                                    if (!node || !(node instanceof HTMLElement)) return;

                                    const id = parseInt(node.getAttribute('data-price-range-id'));
                                    var startInput = document.getElementById('price-range-start-' + id);
                                    startInput.addEventListener('input', priceRangeStartInputHandler);
                                    startInput.addEventListener('change', priceRangeStartChangeHandler);
                                    var endInput = document.getElementById('price-range-end-' + id);
                                    endInput.addEventListener('input', priceRangeEndInputHandler);
                                    endInput.addEventListener('change', priceRangeEndChangeHandler);

                                    requestAnimationFrame(() => {
                                        const sibling = node.previousElementSibling;
                                        if (sibling && sibling instanceof HTMLElement) {
                                            const sibStartInput = sibling.querySelector('[id^="price-range-start-"]');
                                            const sibEndInput = sibling.querySelector('[id^="price-range-end-"]');
                                            sibEndInput.value = 0;
                                            sibStartInput.dispatchEvent(new Event('change', { bubbles: true }));
                                        }
                                    });
                                });
                                mutation.removedNodes.forEach((node) => {
                                    if (!node || !(node instanceof HTMLElement)) return;

                                    const id = parseInt(node.getAttribute('data-price-range-id'));
                                    var startInput = node.querySelector('[id^="price-range-start-"]');
                                    startInput.removeEventListener('input', priceRangeStartInputHandler);
                                    startInput.removeEventListener('change', priceRangeStartChangeHandler);
                                    var endInput = node.querySelector('[id^="price-range-end-"]');
                                    endInput.removeEventListener('input', priceRangeEndInputHandler);
                                    endInput.removeEventListener('change', priceRangeEndChangeHandler);

                                    requestAnimationFrame(() => {
                                        const lastOnList = priceRangeList.lastElementChild;
                                        if (lastOnList && lastOnList instanceof HTMLElement) {
                                            const lastEndInput = lastOnList.querySelector('[id^="price-range-end-"]');

                                            lastEndInput.type = 'text';
                                            lastEndInput.value = '∞';
                                        }
                                    });
                                });
                            });
                        });
                        observer.observe(priceRangeList , { childList: true });
                    </script>
                    
                    <flux:button variant="subtle" icon="plus" class="mr-9" wire:click.prevent="addShippingCost" id="add-shipping-cost-button">
                        {{ __('Add Shipping Cost') }}
                    </flux:button>
                    @error('costs')
                        <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                    @enderror
                    <div class="flex items-center gap-4 mt-2">
                        <div class="flex items-center justify-end">
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}
                            </flux:button>
                        </div>

                        <x-action-message class="me-3" on="shipping-updated">
                            {{ __('Saved.') }}
                        </x-action-message>
                    </div>
                </div>
            </form>
        </div>
    </x-business.layout>
</section>
