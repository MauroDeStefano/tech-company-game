<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('user')?->id;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->user()->id;
        
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'current_password' => [
                'required_with:password',
                'string',
                'current_password'
            ],
            'password' => [
                'sometimes',
                'required',
                'confirmed',
                Password::defaults()
            ],
        ];
    }
}