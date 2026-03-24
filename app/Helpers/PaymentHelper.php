<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Validator;

class PaymentHelper
{
    public static function simulate(string $method, array $data): bool
    {
        return match ($method) {
            'visa' => Validator::make($data, [
                'card_number' => ['required', 'digits:16'],
                'cvc' => ['required', 'digits:3'],
            ])->passes(),

            'paypal' => Validator::make($data, [
                'email' => ['required', 'email'],
            ])->passes(),

            'mbway' => Validator::make($data, [
                'phone' => ['required', 'regex:/^9\d{8}$/'],
            ])->passes(),

            default => false,
        };
    }
}
