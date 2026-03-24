<x-layouts.main-content title="Recharge Card" heading="Recharge your virtual card">
    <form action="{{ route('card.recharge.submit') }}" method="POST" class="space-y-6 max-w-xl mx-auto bg-white dark:bg-zinc-800 p-8 rounded-xl shadow">
        @csrf

        <div>
            <label for="method" class="block font-semibold mb-1">Payment Method</label>
            <select name="method" id="method" class="w-full border rounded p-2 focus:ring-2 focus:ring-emerald-500" required>
                <option value="Visa" {{ $user->default_payment_type == 'Visa' ? 'selected' : '' }}>Visa</option>
                <option value="PayPal" {{ $user->default_payment_type == 'PayPal' ? 'selected' : '' }}>PayPal</option>
                <option value="MB WAY" {{ $user->default_payment_type == 'MB WAY' ? 'selected' : '' }}>MB WAY</option>
            </select>
        </div>

        <div id="visa-fields" class="conditional-fields space-y-4 {{ $user->default_payment_type === 'Visa' ? '' : 'hidden' }}">
            <input
                type="text"
                name="card_number"
                placeholder="Card Number (16 digits)"
                maxlength="16"
                pattern="\d{16}"
                class="w-full border rounded p-2 focus:ring-2 focus:ring-emerald-500"
                value="{{ $user->default_payment_type === 'Visa' ? $user->default_payment_reference : '' }}"
                autocomplete="cc-number"
            />
            <input
                type="text"
                name="cvc"
                placeholder="CVC (3 digits)"
                maxlength="3"
                pattern="\d{3}"
                class="w-full border rounded p-2 focus:ring-2 focus:ring-emerald-500"
                autocomplete="cc-csc"
            />
        </div>

        <div id="paypal-fields" class="conditional-fields space-y-4 {{ $user->default_payment_type === 'PayPal' ? '' : 'hidden' }}">
            <input
                type="email"
                name="email"
                placeholder="PayPal Email"
                class="w-full border rounded p-2 focus:ring-2 focus:ring-emerald-500"
                value="{{ $user->default_payment_type === 'PayPal' ? $user->default_payment_reference : '' }}"
                autocomplete="email"
            />
        </div>

        <div id="mbway-fields" class="conditional-fields space-y-4 {{ $user->default_payment_type === 'MB WAY' ? '' : 'hidden' }}">
            <input
                type="text"
                name="phone"
                placeholder="MB WAY Phone (9 digits starting with 9)"
                maxlength="9"
                pattern="9\d{8}"
                class="w-full border rounded p-2 focus:ring-2 focus:ring-emerald-500"
                value="{{ $user->default_payment_type === 'MB WAY' ? $user->default_payment_reference : '' }}"
                autocomplete="tel"
            />
        </div>

        <div>
            <label for="value" class="block font-semibold mb-1">Amount (€)</label>
            <input type="number" name="value" step="0.01" min="0.01" required class="w-full border rounded p-2 focus:ring-2 focus:ring-emerald-500" />
        </div>

        <flux:button type="submit" variant="primary" class="w-full">
            Recharge Card
        </flux:button>
    </form>

    <script>
        const methodSelect = document.getElementById('method');
        const fields = {
            'Visa': document.getElementById('visa-fields'),
            'PayPal': document.getElementById('paypal-fields'),
            'MB WAY': document.getElementById('mbway-fields'),
        };

        methodSelect.addEventListener('change', () => {
            Object.values(fields).forEach(f => f.classList.add('hidden'));
            if (fields[methodSelect.value]) {
                fields[methodSelect.value].classList.remove('hidden');
            }
        });

        methodSelect.dispatchEvent(new Event('change'));
    </script>
</x-layouts.main-content>
