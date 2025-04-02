<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'register_name' => 'required|string|min:2|max:255',
            'register_email' => 'required|string|email|max:255|unique:users,email',
            'register_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'register_password_confirmation' => 'required|string|min:8',
        ];
    }
}
