<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Game;

class CreateGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255|min:3',
            'notes' => 'nullable|string|max:1000',
            'initial_money' => 'nullable|numeric|min:1000|max:50000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'Il nome della partita deve avere almeno 3 caratteri.',
            'name.max' => 'Il nome della partita non può superare 255 caratteri.',
            'notes.max' => 'Le note non possono superare 1000 caratteri.',
            'initial_money.min' => 'Il patrimonio iniziale deve essere almeno 1.000 €.',
            'initial_money.max' => 'Il patrimonio iniziale non può superare 50.000 €.',
            'initial_money.numeric' => 'Il patrimonio deve essere un numero valido.',
        ];
    }

    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();
        
        return array_merge([
            'money' => Game::INITIAL_MONEY,
            'status' => Game::STATUS_ACTIVE,
            'name' => null,
            'notes' => null,
        ], $validated);
    }
}

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
            'notes' => 'sometimes|string|max:1000',
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

class ResumeGameRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paused_at' => 'nullable|integer|min:0', 
            'offline_duration' => 'nullable|integer|min:0', 
        ];
    }

    public function messages(): array
    {
        return [
            'paused_at.integer' => 'Il timestamp deve essere un numero intero.',
            'paused_at.min' => 'Il timestamp deve essere positivo.',
            'offline_duration.integer' => 'La durata offline deve essere un numero intero.',
            'offline_duration.min' => 'La durata offline deve essere positiva.',
        ];
    }

    public function getOfflineDurationSeconds(): int
    {
        return intval(($this->validated()['offline_duration'] ?? 0) / 1000);
    }
}