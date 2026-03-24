<x-layouts.main-content title="Register Employee" heading="Register a new employee"
    subheading='Click on "Register" to save.'>
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <form method="POST" action="{{ route('users.employees.save') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="flex flex-wrap gap-8">
                        <div class="grow mt-6 space-y-4">

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-4">
                                    
                                    <flux:input name="name" :label="__('Name')" type="text" required autofocus
                                        autocomplete="name" :placeholder="__('Full name')" />

                                    
                                    <flux:radio.group name="gender" :label="__('Gender')" variant="segmented"
                                        size="l" class="w-64" required>
                                        <flux:radio label="Male" value="M" />
                                        <flux:radio label="Female" value="F" />
                                    </flux:radio.group>
                                </div>

                                <div class="space-y-4">
                                    
                                    <flux:input name="email" :label="__('Email address')" type="email" required
                                        autocomplete="email" placeholder="email@example.com" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-4">
                                    
                                    <flux:input name="password" :label="__('Password')" type="password" required
                                        autocomplete="new-password" viewable :placeholder="__('Password')" />
                                </div>
                                <div class="space-y-4">
                                    
                                    <flux:input name="password_confirmation" :label="__('Confirm password')" type="password"
                                        required autocomplete="new-password" viewable
                                        :placeholder="__('Confirm password')" />
                                </div>
                            </div>

                            <div class="flex mt-6">
                                <flux:button type="submit" variant="primary">
                                    {{ __('Register') }}
                                </flux:button>
                            </div>
                        </div>
                        
                        
                        <div class="flex-shrink-0">
                            <div class="pb-6 pe-12">
                                <x-field.image name="photo" label="Photo" width="md" :readonly="false"
                                    :deleteAllow="false" :imageUrl="$employee->photoUrl" />
                            </div>
                        </div>
                    </div>
        </div>
        </form>
        </section>
    </div>
    </div>
</x-layouts.main-content>
