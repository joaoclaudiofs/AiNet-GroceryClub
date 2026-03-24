<x-layouts.main-content title="Edit user" heading="Editing"
    :subheading="$user->type . ' ' .  $user->name">
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <form method="POST" action="{{ route('users.update', ['user' => $user])  }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="flex flex-wrap gap-8">
                    <div class="grow mt-6 space-y-4">

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-4">
                                
                                <flux:input name="name" :value="$user->name" :label="__('Name')" type="text" required autofocus
                                    autocomplete="name" :placeholder="__('Full name')" 
                                    :disabled="false"/>
                                
                                
                                <flux:radio.group name="gender" :label="__('Gender')" variant="segmented" size="l" class="w-64"
                                    required
                                    :disabled="false">
                                    <flux:radio label="Male" value="M" :checked="$user->gender == 'M'"/>
                                    <flux:radio label="Female" value="F" :checked="$user->gender == 'F'"/>
                                </flux:radio.group>
                            </div>

                            <div class="space-y-4">
                                
                                <flux:input name="email" :value="$user->email" :label="__('Email address')" type="email" required
                                    autocomplete="email" placeholder="email@example.com" 
                                    :disabled="false"/>
                            </div>
                        </div>

                        @if ($user->type != "employee")
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-4">
                                
                                <flux:input
                                    name="nif"
                                    :value="$user->nif"
                                    :label="__('NIF')"
                                    type="number"
                                    placeholder="NIF"
                                    :disabled="false"
                                />

                                
                                <flux:input
                                    name="default_delivery_address"
                                    :value="$user->default_delivery_address"
                                    :label="__('Delivery address')"
                                    type="text"
                                    placeholder="Delivery address"
                                    :disabled="false"
                                />
                            </div>

                            <div class="space-y-4">
                                
                                <flux:select 
                                    name="default_payment_type"
                                    :value="$user->default_payment_type" 
                                    placeholder="Choose a payment type..."
                                    :label="__('Payment type')"
                                    :disabled="false">
                                    <flux:select.option value="" :selected="true">None</flux:select.option>
                                    <flux:select.option :selected="$user->default_payment_type == 'Visa'">Visa</flux:select.option>
                                    <flux:select.option :selected="$user->default_payment_type == 'PayPal'">PayPal</flux:select.option>
                                    <flux:select.option :selected="$user->default_payment_type == 'MB WAY'">MB WAY</flux:select.option>
                                </flux:select>

                                <flux:input
                                    name="default_payment_reference"
                                    :value="$user->default_payment_reference"
                                    :label="__('Payment reference')"
                                    type="text"
                                    placeholder="Payment reference"
                                    icon:trailing="credit-card"
                                    :disabled="false"
                                />
                            </div>
                        </div>
                        @endif

                        <div class="flex mt-6">
                            <flux:button type="submit" variant="primary">
                                {{ __('Update') }}
                            </flux:button>
                        </div>

                        @if ($errors->any())
                        <div class="text-red-600">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    </div>


                    
                    <div class="flex-shrink-0">
                        <div class="pb-6 pe-12">
                            <x-field.image name="photo" label="Photo" width="md" :readonly="false"
                                :deleteAllow="false" :imageUrl="$user->photoUrl" />
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-layouts.main-content>
