<?php

namespace App\Http\Requests\game;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Game;

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