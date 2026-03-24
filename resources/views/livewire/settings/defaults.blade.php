<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public ?string $default_payment_type = null;
    public ?string $default_payment_reference = null;
    public ?string $default_delivery_address = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->default_payment_type = Auth::user()->default_payment_type;
        $this->default_payment_reference = Auth::user()->default_payment_reference;
        $this->default_delivery_address = Auth::user()->default_delivery_address;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        if ($user->type === 'employee') {
            return;
        }

        $validated = $this->validate([
            'default_payment_type' => [
                'nullable',
                'in:"Visa","PayPal","MB WAY"',
                'max:255'
            ],
            
            'default_payment_reference' => [
                'nullable',
                'string',
                'max:255' 
            ],

            'default_delivery_address' => [
                'nullable',
                'string',
                'max:255'
            ]
        ]);
        $validated['default_payment_type'] = $validated['default_payment_type'] ?: null;

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }
}; ?>

@php
    function isEmployee(): bool
    {
        return auth()->user()->type === 'employee';
    }
@endphp

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Defaults')" :subheading="__('Update your default payment and delivery information')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            @csrf
            
            <flux:input
                wire:model="default_delivery_address"
                :label="__('Delivery address')"
                type="text"
                placeholder="Delivery address"
                :disabled="isEmployee()"
            />

            <flux:select 
                wire:model="default_payment_type" 
                placeholder="Choose a payment type..."
                :label="__('Payment type')"
                :disabled="isEmployee()">
                <flux:select.option value="">None</flux:select.option>
                <flux:select.option value="Visa">Visa</flux:select.option>
                <flux:select.option value="PayPal">PayPal</flux:select.option>
                <flux:select.option value="MB WAY">MB WAY</flux:select.option>
            </flux:select>

            <flux:input
                wire:model="default_payment_reference"
                :label="__('Payment reference')"
                type="text"
                placeholder="Payment reference"
                icon:trailing="credit-card"
                autocomplete="default_payment_reference"
                :disabled="isEmployee()"
            />

            <div class="flex items-center gap-4">
                @if (!isEmployee())
                    <div class="flex items-center justify-end">
                        <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                    </div>
                @endif

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
