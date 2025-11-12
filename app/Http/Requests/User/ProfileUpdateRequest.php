<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // phone/contact captured during registration; allow optional update here
            // enforce exactly 11 digits when provided
            'contact' => ['nullable', 'digits:11'],
        ];
    }
}
