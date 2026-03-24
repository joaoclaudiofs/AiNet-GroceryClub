<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800 g">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('home') }}" class="mb-0" wire:navigate>
                <x-app-logo />
            </a>

            
            <flux:navlist>
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>{{ __('Home') }}</flux:navlist.item>
                    <flux:navlist.item icon="shopping-bag" :href="route('shop')" :current="request()->routeIs('shop')" wire:navigate>{{ __('Shop') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            
            @if (auth()->user()?->type == "employee" || auth()->user()?->type == "board")
            <flux:navlist>
                <flux:navlist.group :heading="__('Management')" class="grid">
                    <flux:navlist.item icon="cube" :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>{{ __('Products') }}</flux:navlist.item>
                    <flux:navlist.item icon="tag" :href="route('categories.index')" :current="request()->routeIs('categories.*')" wire:navigate>{{ __('Categories') }}</flux:navlist.item>
                    <flux:navlist.group :heading="__('Settings')" expandable :expanded="false">
                            <flux:navlist.item href="#" class="font-light font-sm">{{ __('Membership fee') }}</flux:navlist.item>
                            <flux:navlist.item href="#" class="font-light font-sm">{{ __('Shipping cost') }}</flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist.group>
            </flux:navlist>

            <flux:navlist>
                <flux:navlist.group :heading="__('History')" class="grid">
                    <flux:navlist.item :href="'#'" :current="false" wire:navigate>{{ __('Operations') }}</flux:navlist.item>
                    <flux:navlist.item :href="'#'" :current="false" wire:navigate>{{ __('Orders') }}</flux:navlist.item>
                    <flux:navlist.item :href="'#'" :current="false" wire:navigate>{{ __('Stock Adjustment') }}</flux:navlist.item>
                    <flux:navlist.item :href="'#'" :current="false" wire:navigate>{{ __('Item Order') }}</flux:navlist.item>
                    <flux:navlist.item :href="'#'" :current="false" wire:navigate>{{ __('Supply Order') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            @endif

            <flux:spacer/>

            @if (auth()->user()?->type == "board")
            <flux:navlist>
                <flux:navlist.group :heading="'Users'" class="grid">
                    <flux:navlist.item icon="user" :href="'#'" :current="false" wire:navigate>Members</flux:navlist.item>
                    <flux:navlist.item icon="shield-check" :href="'#'" :current="false" wire:navigate>Employees</flux:navlist.item>
                    <flux:navlist.item icon="briefcase" :href="'#'" :current="false" wire:navigate>Board</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            @endif

            <!-- <flux:navlist variant="outline">
                <flux:navlist.group :heading="'My Content'" class="grid">
                    <flux:navlist.item icon="document" icon:variant="solid"  :href="'#'" :current="false" wire:navigate>My Disciplines</flux:navlist.item>
                    <flux:navlist.item icon="user"  icon:variant="solid"  :href="'#'" :current="false" wire:navigate>My Teachers</flux:navlist.item>
                    <flux:navlist.item icon="users" icon:variant="solid" :href="'#'" :current="false" wire:navigate>My Students</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist> -->

            
            @auth
                <flux:dropdown position="bottom" align="start">
                    <flux:profile
                        :name="auth()->user()?->firstLastName()"
                        :initials="auth()->user()?->firstLastInitial()"
                        icon-trailing="chevron-up-down"
                    />

                    <flux:menu class="w-[220px]">
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()?->firstLastInitial()}}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()?->name}}</span>
                                        <span class="truncate text-xs">{{ auth()->user()?->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>


                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @else
                <flux:navlist variant="outline">
                    <flux:navlist.group :heading="'Authentication'" class="grid">
                        <flux:navlist.item icon="key" :href="route('login')" :current="request()->routeIs('login')" wire:navigate>Login</flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            @endauth
        </flux:sidebar>

        
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            @auth
            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()?->firstLastInitial()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()?->firstLastInitial()}}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()?->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()?->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
            @else
                <flux:navbar>
                    <flux:navbar.item  icon="key" :href="route('login')" :current="request()->routeIs('login')" wire:navigate>Login</flux:navbar.item>
                </flux:navbar>
            @endauth
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
