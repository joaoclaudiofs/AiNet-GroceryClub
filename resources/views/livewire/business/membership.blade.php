<?php

use App\Models\Settings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Traits\UserPhotoFileStorage;

new class extends Component {

    public float $fee = 0.0;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->fee = Settings::first('membership_fee')['membership_fee'] ?? 0.0;
    }

    /**
     * Update the membership information for the currently authenticated user.
     */
    public function updateMembershipInformation(): void
    {
        $validated = $this->validate([
            'fee' => ['required', 'numeric', 'min:0']
        ]);


        $settings =  Settings::updateOrCreate(
            ['id' => 1],
            ['membership_fee' => $validated['fee']]
        );
        $settings->save();

        $this->dispatch('membership-updated', fee: $validated['fee']);
    }
}; ?>


<section class="w-full">
    @include('partials.business-heading')

    <x-business.layout :heading="__('Membership')" :subheading="__('Configure the membership settings for your business.')">
            <div>
                <form wire:submit="updateMembershipInformation" class="my-6 w-full space-y-6">
                    @csrf
                    <flux:input wire:model="fee" :label="__('Membership Fee')" type="number" step="0.01" required />

                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-end">
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}
                            </flux:button>
                        </div>

                        <x-action-message class="me-3" on="membership-updated">
                            {{ __('Saved.') }}
                        </x-action-message>
                    </div>
                </form>
        </div>
    </x-business.layout>
</section>
