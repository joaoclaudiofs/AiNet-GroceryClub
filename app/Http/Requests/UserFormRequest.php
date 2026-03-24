<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class UserFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id),],
            'password' => ['nullable', 'string', 'confirmed', Rules\Password::defaults()],
            'gender' => ['required', 'in:M,F'],
            'default_delivery_address' => ['nullable', 'string', 'max:255'],
            'nif' => ['nullable', 'numeric', 'digits:9'],
            'default_payment_type' => ['nullable', 'in:Visa,PayPal,MB WAY', 'max:255'],
            'default_payment_reference' => ['nullable', 'string', 'max:255'],
            'photo' => ['sometimes', 'image', 'max:4096'],
        ];
    }


}
