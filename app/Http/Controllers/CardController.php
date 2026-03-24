<?php

namespace App\Http\Controllers;

use App\Services\Payment;
use App\Models\Card;
use App\Models\Operation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\RedirectResponse;
use App\Models\Settings;

class CardController extends Controller
{
    public function form()
    {
        $user = Auth::user();

        return view('card.recharge', [
            'user' => $user,
        ]);
    }

    public function recharge(Request $request)
    {
        $request->validate([
            'value' => 'required|numeric|min:0.01',
        ]);

        $data = $request->only(['card_number', 'cvc', 'email', 'phone']);
        $method = $request->input('method');
        $amount = $request->input('value');


        $paymentValid = match ($method) {
            'Visa' => Payment::payWithVisa($data['card_number'] ?? null, $data['cvc'] ?? null),
            'PayPal' => Payment::payWithPaypal($data['email'] ?? null),
            'MB WAY' => Payment::payWithMBway($data['phone'] ?? null),
            default => false,
        };

        if (!$paymentValid) {
            return back()->with('alert-type', 'danger')->with('alert-msg', 'Payment failed: invalid payment details.');
        }

        $card = Card::find(Auth::id());

        if (!$card) {
            return back()->with('alert-type', 'danger')->with('alert-msg', 'Card not found.');
        }

        $card->balance += $amount;
        $card->save();

        Operation::create([
            'card_id' => $card->id,
            'type' => 'credit',
            'value' => $amount,
            'date' => now(),
            'credit_type' => 'payment',
            'payment_type' => $method,
            'payment_reference' => $data[$this->getReferenceKey($method)] ?? null,
        ]);

        return redirect()->route('card.index')->with('alert-type', 'success')->with('alert-msg', 'Card recharged successfully.');
    }

    private function getReferenceKey(string $method): string
    {
        return match ($method) {
            'visa' => 'card_number',
            'paypal' => 'email',
            'mbway' => 'phone',
            default => 'reference',
        };
    }

    public function index()
    {
        $card = Card::find(Auth::id());

        if (!$card) {
            return redirect()->back()->with('alert-type', 'warning')->with('alert-msg', 'Card not found.');
        }

        $operations = Operation::where('card_id', $card->id)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('card.index', [
            'operations' => $operations,
            'totalSpent' => $operations->where('type', 'debit')->sum('value'),
            'latestPurchase' => $operations->where('type', 'debit')->first(),
        ]);
    }


    public function exportCsv()
    {
        $card = Card::find(Auth::id());

        if (!$card) {
            return redirect()->back()->with('alert-type', 'warning')->with('alert-msg', 'Card not found.');
        }

        $operations = Operation::where('card_id', $card->id)
            ->orderBy('date', 'desc')
            ->get();

        $csvData = [];

        $csvData[] = ['Date', 'Type', 'Value (€)', 'Credit Type', 'Payment Type', 'Payment Reference'];

        foreach ($operations as $op) {
            $csvData[] = [
                $op->date,
                ucfirst($op->type),
                number_format($op->value, 2),
                $op->credit_type ?? '',
                $op->payment_type ?? '',
                $op->payment_reference ?? '',
            ];
        }

        $handle = fopen('php://temp', 'r+');
        foreach ($csvData as $line) {
            fputcsv($handle, $line);
        }
        rewind($handle);
        $csvOutput = stream_get_contents($handle);
        fclose($handle);

        $filename = 'purchase_history_' . now()->format('Ymd_His') . '.csv';

        return Response::make($csvOutput, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    public function membership(): RedirectResponse
    {
        $user = Auth::user();
        if ($user->type != "pending_member") {
            return redirect()->back()
                ->with('alert-type', 'danger')
                ->with('alert-msg', 'Unauthorized access!');
        }

        if ($user->email_verified_at == null) {
            return redirect()->back()
                ->with('alert-type', 'danger')
                ->with('alert-msg', 'Please verify your email!');
        }

        $card = $user->card;
        $membershipFee = Settings::first('membership_fee')['membership_fee'] ?? 0.0;
        if ($card->balance >= $membershipFee) {
            $card->operations()->create([
                'type' => 'debit',
                'value' => $membershipFee,
                'date' => now()->format('Y-m-d'),
                'debit_type' => 'membership_fee'
            ]);
            $card->balance -= $membershipFee;
            $card->save();

            $user->type = 'member';
            $user->save();

            return redirect()->back()
                ->with('alert-type', 'success')
                ->with('alert-msg', 'Membership bought!');
        }

        return redirect()->back()
            ->with('alert-type', 'danger')
            ->with('alert-msg', 'Insufficient funds! Membership fee is ' . $membershipFee);
    }
}
