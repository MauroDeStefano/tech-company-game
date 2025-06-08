<?php

namespace App\Http\Requests\game;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Game;

class PauseGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paused_at' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'paused_at.integer' => 'Il timestamp deve essere un numero intero.',
            'paused_at.min' => 'Il timestamp deve essere positivo.',
        ];
    }
}