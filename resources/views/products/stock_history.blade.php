@php
    $badgeColors = collect([
        'red', 'orange', 'amber', 'yellow',
        'green', 'emerald', 'teal', 'cyan',
        'sky', 'blue', 'indigo', 'violet',
        'purple', 'fuchsia', 'pink', 'rose'
    ]);
@endphp

<x-layouts.main-content title="Stock History" heading="Stock Adjustments History" subheading="All stock changes for products">
    <div class="flex gap-4 w-full flex-1 flex-col rounded-xl">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-700 dark:text-gray-200">Stock Adjustments</h2>
        </div>
        <div class="font-base text-sm text-gray-700 dark:text-gray-300">
            <table class="w-full table-auto border-collapse">
                <colgroup>
                    <col class="w-2/8">
                    <col class="w-2/8">
                    <col class="w-1/8">
                    <col class="w-2/8">
                    <col class="w-1/8">
                </colgroup>
                <thead>
                    <tr class="bg-zinc-100 dark:bg-zinc-700 text-gray-800 dark:text-gray-200 font-semibold rounded-lg shadow-xs ring-1 ring-zinc-200 dark:ring-zinc-600">
                        <th class="px-2 py-2 text-left rounded-l-lg">Date</th>
                        <th class="px-2 py-2 text-left">Product</th>
                        <th class="px-2 py-2 text-left">Change</th>
                        <th class="px-2 py-2 text-left">Registered By</th>
                        <th class="px-2 py-2 text-left rounded-r-lg">Type</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                        <tr class="border-b border-b-zinc-100 dark:border-b-zinc-700">
                            <td class="px-2 py-2 text-left">{{ $adj->created_at ? \Carbon\Carbon::parse($adj->created_at)->format('Y-m-d H:i') : '-' }}</td>
                            <td class="px-2 py-2 text-left">
                                @if($adj->product)
                                    <flux:badge :color="$badgeColors[$adj->product->id % $badgeColors->count()]" size="sm">
                                        {{ $adj->product->name }}
                                    </flux:badge>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 text-left">
                                <span class="font-mono {{ $adj->quantity_changed > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $adj->quantity_changed > 0 ? '+' : '' }}{{ $adj->quantity_changed }}
                                </span>
                            </td>
                            <td class="px-2 py-2 text-left">
                                @if($adj->user)
                                    <span>{{ $adj->user->name }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 text-left">
                                <span class="capitalize">{{ str_replace('_', ' ', $adj->log_type) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 dark:text-gray-400 py-8">
                                No stock adjustments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">
                {{ $adjustments->links() }}
            </div>
        </div>
    </div>
</x-layouts.main-content>
