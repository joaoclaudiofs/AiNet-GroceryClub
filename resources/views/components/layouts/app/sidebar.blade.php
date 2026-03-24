@php
    $type = auth()?->user()?->type ?? 'guest';
    $isMember = $type == "member" || $type == "pending_member";
    $isPending = $type == "pending_member";

    $isAdmin = $type == "employee" || $type == "board";
    $isEmployee = $type == "employee";
    $isBoard = $type == "board";
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen bg-white dark:bg-zinc-800 g">
        <flux:sidebar sticky stashable
            class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('home') }}" class="mb-0" wire:navigate>
                <x-app-logo />
            </a>


            <flux:navlist>
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="shopping-bag" :href="route('shop.index')" :current="request()->routeIs('shop.index')" wire:navigate>
                        {{ __('Shop') }}
                    </flux:navlist.item>
                    <flux:navlist.item id="cart-badge" icon="shopping-cart" :badge="$cartCount" :href="route('cart.show')" :current="request()->routeIs('cart.show')" wire:navigate>
                        {{ __('Cart') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>


            @if ($isAdmin)
                <flux:navlist>
                    <flux:navlist.group :heading="__('Management')" class="grid">
                        @if ($isBoard)
                            <flux:navlist.item icon="cube" :href="route('products.index')" :current="request()->is('management/products') and request()->routeIs('products.*')" wire:navigate>
                                {{ __('Products') }}
                            </flux:navlist.item>
                            <flux:navlist.item icon="tag" :href="route('categories.index')" :current="request()->routeIs('categories.*')" wire:navigate>
                                {{ __('Categories') }}
                            </flux:navlist.item>
                        @endif
                        <flux:navlist.item icon="clipboard-document" :badge="\App\Models\Product::whereColumn('stock', '<', 'stock_lower_limit')->count()" :href="route('products.stock')" :current="request()->routeIs('products.stock')" wire:navigate>
                                {{ __('Stock') }}
                        </flux:navlist.item>
                        <flux:navlist.item icon="truck" :badge="\App\Models\Order::where('status', '=', 'pending')->count()" :href="route('orders.index')" :current="request()->is('management/orders') and request()->routeIs('orders.*')" wire:navigate>
                                {{ __('Orders') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>

                @if ($isBoard)
                    <flux:navlist>
                       <flux:navlist.group :heading="__('Metrics & History')" class="grid">
                            <flux:navlist.item :href="route('metricsHistory.transactionRecords')" :current="request()->routeIs('metricsHistory.transactionRecords')" wire:navigate>
                                {{ __('Transaction Records') }}
                            </flux:navlist.item>
                            <flux:navlist.item :href="route('metricsHistory.salesPerformance')" :current="request()->routeIs('metricsHistory.salesPerformance')" wire:navigate>
                                {{ __('Sales Performance') }}
                            </flux:navlist.item>
                            <flux:navlist.item :href="route('metricsHistory.membershipTrends')" :current="request()->routeIs('metricsHistory.membershipTrends')" wire:navigate>
                                {{ __('Membership Trends') }}
                            </flux:navlist.item>
                              <flux:navlist.item :href="route('metricsHistory.stockHistory')" :current="request()->routeIs('metricsHistory.stockHistory')" wire:navigate>
                                {{ __('Stock History') }}
                            </flux:navlist.item>
                       </flux:navlist.group>
                    </flux:navlist>
                @endif
            @endif

            @if ($isBoard)
                <flux:navlist>
                    <flux:navlist.group :heading="'Users'" class="grid">
                        <flux:navlist.item icon="user" :href="route('users.members')" :current="request()->routeIs('users.members')" wire:navigate>
                            Members
                        </flux:navlist.item>
                        <flux:navlist.item icon="shield-check" :href="route('users.employees')" :current="request()->routeIs('users.employees')" wire:navigate>
                            Employees
                        </flux:navlist.item>
                        <flux:navlist.item icon="briefcase" :href="route('users.boards')" :current="request()->routeIs('users.boards')" wire:navigate>
                            Board
                        </flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            @endif

            <flux:spacer />

            @if ($isMember || $isBoard)
                <flux:navlist>
                    <flux:navlist.item
                        icon="credit-card"
                        href="{{ route('card.index') }}"
                        :current="request()->routeIs('card.index')"
                        badge="{{ number_format(auth()->user()?->card->balance ?? 0, 2, ',', '.') . ' €' }}"
                        >
                        {{ __('My Card') }}
                    </flux:navlist.item>

                    <flux:navlist.item
                        icon="truck"
                        :href="route('orders.user')"
                        :current="request()->routeIs('orders.user')"
                    >
                        {{ __('My Orders') }}
                    </flux:navlist.item>
                    @if ($isBoard)
                        <flux:navlist.group class="grid">
                            <flux:navlist.item icon="wrench-screwdriver" :href="route('business.membership')" :current="request()->routeIs('business.*')" wire:navigate>
                                {{ __('Business Settings') }}
                            </flux:navlist.item>
                        </flux:navlist.group>
                    @endif
                </flux:navlist>
            @endif


            @auth
                <flux:dropdown position="bottom" align="start">
                    <flux:profile :avatar="auth()->user()?->photo_url" :name="auth()->user()?->firstLastName()"
                        icon-trailing="chevron-up-down" />

                    <flux:menu class="w-[220px]">
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            <flux:avatar :src="auth()->user()?->photo_url" class="h-full w-full" />
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
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}
                            </flux:menu.item>
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
                        <flux:navlist.item icon="key" :href="route('login')" :current="request()->routeIs('login')"
                            wire:navigate>Login</flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
            @endauth
        </flux:sidebar>


        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            @auth
                <flux:dropdown position="top" align="end">
                    <flux:profile :avatar="auth()->user()?->photo_url" icon-trailing="chevron-down" />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            <flux:avatar :src="auth()->user()?->photo_url" class="h-full w-full" />
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
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}
                            </flux:menu.item>
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
                    <flux:navbar.item icon="key" :href="route('login')" :current="request()->routeIs('login')"
                        wire:navigate>Login</flux:navbar.item>
                </flux:navbar>
            @endauth
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>

</html>
