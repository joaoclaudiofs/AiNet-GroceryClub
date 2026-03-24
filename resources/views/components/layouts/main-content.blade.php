<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main class="relative h-full overflow-y-hidden">
        @include('partials.main-content-headings')
        @include('partials.main-content-alerts')
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
