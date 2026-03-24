
@php
    $badgeColors = collect([
        'red', 'orange', 'amber', 'yellow',
        'green', 'emerald', 'teal', 'cyan',
        'sky', 'blue', 'indigo', 'violet',
        'purple', 'fuchsia', 'pink', 'rose'
    ]);
@endphp

<x-layouts.main-content title="Sales" heading="Sales Performance" subheading="Resumo das vendas mensais do clube">
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap gap-4">
            <div class="bg-green-100 text-green-800 px-6 py-4 rounded-lg font-bold text-lg shadow">
                Total: €{{ number_format($salesByMonth->sum('total_sales'), 2) }}
            </div>
            <div class="bg-blue-100 text-blue-800 px-6 py-4 rounded-lg font-bold text-lg shadow">
                Total of Sales: {{ $salesByMonth->sum('sales_count') }}
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($salesByMonth->filter(fn($r) => $r->sales_count > 0) as $record)
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow p-5 flex flex-col gap-2 border border-zinc-100 dark:border-zinc-700">
                    <div class="flex items-center gap-2">
                        <flux:badge :color="$badgeColors[($record->month-1) % $badgeColors->count()]" size="sm" variant="pill">
                            {{ \Carbon\Carbon::createFromDate($record->year, $record->month)->locale('pt_PT')->isoFormat('MMMM') }}
                        </flux:badge>
                        <span class="text-gray-500 dark:text-gray-400 text-xs">{{ $record->year }}</span>
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-2xl font-bold text-green-700 dark:text-green-400">€{{ number_format($record->total_sales, 2) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:badge color="blue" size="xs" variant="subtle">
                            {{ $record->sales_count }} sales
                        </flux:badge>
                    </div>
                </div>
            @endforeach
            @if($salesByMonth->filter(fn($r) => $r->sales_count > 0)->isEmpty())
                <div class="col-span-full text-center text-gray-500 dark:text-gray-400 py-8">
                    No sales found.
                </div>
            @endif

        </div>
    </div>
</x-layouts.main-content>
