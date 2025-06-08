<?php

namespace App\Http\Requests\game;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Game;

class UpdateGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255|min:3',
            'notes' => 'sometimes|string|max:1000|nullable',
            'status' => 'sometimes|in:' . implode(',', [
                Game::STATUS_ACTIVE,
                Game::STATUS_PAUSED,
                Game::STATUS_GAME_OVER
            ]),
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'Il nome della partita deve avere almeno 3 caratteri.',
            'name.max' => 'Il nome della partita non può superare 255 caratteri.',
            'notes.max' => 'Le note non possono superare 1000 caratteri.',
            'status.in' => 'Lo stato deve essere uno tra: active, paused, game_over.',
        ];
    }
}