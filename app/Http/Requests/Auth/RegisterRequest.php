<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in([User::ROLE_CUSTOMER, User::ROLE_VENDOR])],
            'phone' => ['nullable', 'string', 'max:30'],
            'store_name' => ['required_if:role,vendor', 'nullable', 'string', 'max:255', 'unique:stores,name'],
            'store_description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
