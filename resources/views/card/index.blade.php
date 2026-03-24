<x-layouts.main-content title="My Card & Purchase History"
                        heading="My Card"
                        subheading="{{ auth()->user()->name }}">

    <div class="flex flex-col gap-8">

        <div class="flex flex-wrap gap-4 justify-between items-center">
            <div class="flex gap-4">
                <form action="{{ route('card.recharge') }}">
                    @csrf
                <flux:button type="submit" variant="primary" icon="plus" class="w-full">
                        Recharge Card
                    </flux:button>
                </form>

                @if (Auth::user()->type == "pending_member")
                    <form action="{{ route('card.membership') }}">
                        <flux:button type="submit" variant="primary" icon="banknotes" class="w-full">
                            Pay membership fee
                        </flux:button>
                    </form>
                @endif
            </div>

            <form action="{{ route('card.export.csv') }}" method="GET">
                @csrf
                <flux:button type="submit" variant="outline" icon="arrow-down-tray" class="w-full">
                    Export Purchase History (CSV)
                </flux:button>
            </form>
        </div>

        <div class="border p-6 rounded-lg shadow bg-white dark:bg-zinc-800">
            <h2 class="text-lg font-semibold mb-4">Card Details</h2>
            <div class="flex items-center gap-4">
                <span class="text-2xl font-bold text-green-700 dark:text-green-400">
                    {{ number_format(auth()->user()->card->balance ?? 0, 2, ',', '.') }} €
                </span>
                <flux:badge color="emerald" size="sm" icon="credit-card">
                    Balance
                </flux:badge>
            </div>
        </div>

        <div class="border p-6 rounded-lg shadow bg-white dark:bg-zinc-800">
            <h2 class="text-lg font-semibold mb-4">Purchase History</h2>

            @if($operations->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">No operations.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full table-auto border-collapse font-base text-sm text-gray-700 dark:text-gray-300">
                        <thead>
                            <tr class="bg-zinc-100 dark:bg-zinc-700 text-gray-800 dark:text-gray-200 font-semibold rounded-lg shadow-xs ring-1 ring-zinc-200 dark:ring-zinc-600">
                                <th class="px-4 py-2 text-left rounded-l-lg">Date</th>
                                <th class="px-4 py-2 text-left">Type</th>
                                <th class="px-4 py-2 text-left">Value</th>
                                <th class="px-4 py-2 text-left rounded-r-lg">Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($operations as $op)
                                <tr class="border-b border-b-zinc-100 dark:border-b-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($op->date)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2">
                                        <flux:badge :color="$op->type === 'debit' ? 'red' : 'green'" size="sm">
                                            {{ ucfirst($op->type) }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-4 py-2 font-mono">
                                        @if($op->type === 'debit')
                                            <span class="text-red-600 dark:text-red-400">-{{ number_format($op->value, 2, ',', '.') }} €</span>
                                        @else
                                            <span class="text-green-700 dark:text-green-400">+{{ number_format($op->value, 2, ',', '.') }} €</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">{{ $op->payment_type ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $operations->links() }}
                </div>

                <div class="mt-4 space-y-2">
                    <p><strong>Total spent:</strong> <span class="font-mono">{{ number_format($totalSpent, 2, ',', '.') }} €</span></p>
                    @if($latestPurchase)
                        <p><strong>Last purchase:</strong> {{ \Carbon\Carbon::parse($latestPurchase->date)->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-layouts.main-content>
