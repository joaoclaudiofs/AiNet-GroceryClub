<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Traits\UserPhotoFileStorage;

new class extends Component {
    use UserPhotoFileStorage;
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $nif = null;
    public string $gender = '';
    public string $photoUrl = '';
    public $photo = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->nif = Auth::user()->nif;
        $this->gender = Auth::user()->gender;
        $this->photoUrl = Auth::user()->photoUrl;
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
            'name' => ['required', 'string', 'max:255'],

            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],

            'nif' => ['nullable', 'numeric', 'digits:9'],

            'gender' => ['required', 'in:M,F'],

            'photo' => ['nullable', 'image', 'max:4096'],
        ]);


        $photoFile = $validated['photo'] ?? null;
        unset($validated['photo']);


        if ($photoFile) {
            $this->storeUserPhoto($photoFile, $user);
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();


        $this->photoUrl = $user->photoUrl;

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function deletePhoto(): void
    {
        $user = Auth::user();

        if ($user->type === 'employee') {
            return;
        }

        $this->deleteUserPhoto($user);


        $this->photoUrl = $user->photoUrl;

        $this->dispatch('photo-deleted', name: $user->name);
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

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name, email address, gender, nif and photo')">
        <div class="flex flex-col md:flex-row gap-10">
            <div>
                <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
                    @csrf
                    <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus
                        autocomplete="name" :disabled="isEmployee()" />

                    
                    <div>
                        <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email"
                            :disabled="isEmployee()" />

                        @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                            <div>
                                <flux:text class="mt-4">
                                    {{ __('Your email address is unverified.') }}

                                    <flux:link class="text-sm cursor-pointer"
                                        wire:click.prevent="resendVerificationNotification">
                                        {{ __('Click here to re-send the verification email.') }}
                                    </flux:link>
                                </flux:text>

                                @if (session('status') === 'verification-link-sent')
                                    <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                        {{ __('A new verification link has been sent to your email address.') }}
                                    </flux:text>
                                @endif
                            </div>
                        @endif
                    </div>

                    
                    <flux:radio.group wire:model="gender" :label="__('Gender')" variant="segmented" size="sm"
                        required :disabled="isEmployee()">
                        <flux:radio label="Male" value="M" />
                        <flux:radio label="Female" value="F" />
                    </flux:radio.group>

                    
                    @if (!isEmployee())
                    <flux:input wire:model="nif" :label="__('NIF')" type="numeric" autocomplete="nif"
                        :disabled="isEmployee()" />
                    @endif

                    <div class="flex items-center gap-4">
                        @if (!isEmployee())
                            <div class="flex items-center justify-end">
                                <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}
                                </flux:button>
                            </div>
                        @endif

                        <x-action-message class="me-3" on="profile-updated">
                            {{ __('Saved.') }}
                        </x-action-message>
                    </div>
                </form>
                @if (!isEmployee())
                    <livewire:settings.delete-user-form />
                @endif
            </div>

            <div>
                
                <div class="flex justify-center items-center order-2">
                    <x-field.image :imageUrl="$photoUrl" :label="__('Photo')" width="full" :readonly="true"
                        class="rounded-lg shadow-lg max-h-[400px] object-contain" />
                </div>
                <br>
                @if (!isEmployee())
                    <flux:input
                        wire:model="photo"
                        type="file"
                    />
                    <flux:button
                        variant="danger"
                        wire:click="deletePhoto"
                    >
                        {{ __('Delete Photo') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </x-settings.layout>
</section>
