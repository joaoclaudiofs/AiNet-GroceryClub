<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartConfirmationFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;  // Ajusta aqui se quiseres autorização personalizada
    }

    public function rules(): array
    {
        return [
            'nif' => 'required|string|min:9|max:9',              // Exemplo: NIF com 9 caracteres
            'address' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nif.required' => 'NIF is required.',
            'nif.min' => 'NIF must be 9 characters.',
            'nif.max' => 'NIF must be 9 characters.',
            'address.required' => 'Delivery address is required.',
            'address.max' => 'Address cannot be longer than 255 characters.',
        ];
    }
}
