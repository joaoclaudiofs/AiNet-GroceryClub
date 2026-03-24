<x-layouts.main-content title="Records" heading="Transaction Records" subheading="">
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="p-2 text-left">ID</th>
                    <th class="p-2 text-left">Date</th>
                    <th class="p-2 text-left">Member</th>
                    <th class="p-2 text-right">Value (€)</th>
                    <th class="p-2 text-left">State</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $transaction)
                    <tr class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                        <td class="p-2 font-mono text-sm text-gray-600 dark:text-gray-300">{{ $transaction->id }}</td>
                        <td class="p-2">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                        <td class="p-2 flex items-center gap-2">
                            @if($transaction->user && $transaction->user->avatar)
                                <img src="{{ $transaction->user->avatar }}" alt="" class="w-6 h-6 rounded-full object-cover">
                            @endif
                            <span>{{ $transaction->user->name ?? '—' }}</span>
                        </td>
                        <td class="p-2 text-right font-semibold text-green-700 dark:text-green-400">
                            €{{ number_format($transaction->total, 2) }}
                        </td>
                        <td class="p-2">
                            @php
                                $statusColors = [
                                    'confirmed' => 'green',
                                    'pending' => 'yellow',
                                    'cancelled' => 'red',
                                    'failed' => 'red',
                                    'processing' => 'blue',
                                ];
                                $color = $statusColors[$transaction->status] ?? 'gray';
                            @endphp
                            <flux:badge color="{{ $color }}" size="sm" variant="pill">
                                {{ ucfirst($transaction->status) }}
                            </flux:badge>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>
</x-layouts.main-content>
