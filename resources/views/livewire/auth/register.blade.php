<?php

use App\Models\User;
use App\Models\Card;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Traits\UserPhotoFileStorage;

new #[Layout('components.layouts.auth')] class extends Component {
    use UserPhotoFileStorage;
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public string $gender = '';
    public string $default_delivery_address = '';
    public $nif = null;
    public ?string $default_payment_type = null;
    public ?string $default_payment_reference = null;
    public $photo = null;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'gender' => ['required', 'in:M,F'],
            'default_delivery_address' => ['nullable', 'string', 'max:255'],
            'nif' => ['nullable', 'numeric', 'digits:9'],
            'default_payment_type' => ['nullable', 'in:"Visa","PayPal","MB WAY"', 'max:255'],
            'default_payment_reference' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $validated['password'] = Hash::make($validated['password']);


        $photoFile = $validated['photo'] ?? null;
        unset($validated['photo']);

        $validated['type'] = 'pending_member';

        $user = User::create($validated);


        if ($photoFile) {
            $this->storeUserPhoto($photoFile, $user);
        }

        // novo card
        $user->card()->create([
            'card_number' => 99999 + $user->id
        ]);

        event(new Registered($user));

        Auth::login($user);

        $this->redirectIntended(route('home', absolute: false), navigate: true);
    }
}; 
?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6" enctype="multipart/form-data">
        @csrf
        
        <flux:input
            wire:model="name"
            :label="__('Name')"
            type="text"
            required
            autofocus
            autocomplete="name"
            :placeholder="__('Full name')"
        />

        
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autocomplete="email"
            placeholder="email@example.com"
        />

        
        <flux:radio.group 
            wire:model="gender"
            :label="__('Gender')" 
            variant="segmented" 
            size="sm"
            required>    
            <flux:radio label="Male" value="M"/>    
            <flux:radio label="Female" value="F"/>    
        </flux:radio.group>

        
        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autocomplete="new-password"
            viewable
            :placeholder="__('Password')"
        />

        
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirm password')"
            type="password"
            required
            autocomplete="new-password"
            viewable
            :placeholder="__('Confirm password')"
        />

        
        <flux:input
            wire:model="default_delivery_address"
            :label="__('Delivery address')"
            type="text"
            placeholder="Delivery address"
        />

        
        <flux:input
            wire:model="nif"
            :label="__('NIF')"
            type="number"
            placeholder="NIF"
        />

        
        <flux:select 
            wire:model="default_payment_type" 
            placeholder="Choose a payment type..."
            :label="__('Payment type')">
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
        />
    
        
        <flux:input
            wire:model="photo"
            :label="__('Photo')"
            type="file"
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('Already have an account?') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>
