<?php

namespace App\Http\Requests\game;

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
            // Il nome è REQUIRED nel frontend, non nullable
            'name' => 'required|string|max:255|min:3',
            'notes' => 'nullable|string|max:1000',
            'initial_money' => 'required|numeric|min:1000|max:50000',
        ];
    }

    public function messages(): array
    {
        return [
            // Messages per 'name'
            'name.required' => 'Il nome della software house è richiesto.',
            'name.min' => 'Il nome della partita deve avere almeno 3 caratteri.',
            'name.max' => 'Il nome della partita non può superare 255 caratteri.',
            'name.string' => 'Il nome deve essere una stringa di testo.',
            
            // Messages per 'notes'
            'notes.max' => 'Le note non possono superare 1000 caratteri.',
            'notes.string' => 'Le note devono essere una stringa di testo.',
            
            // Messages per 'initial_money'
            'initial_money.required' => 'Il patrimonio iniziale è richiesto.',
            'initial_money.min' => 'Il patrimonio iniziale deve essere almeno 1.000 €.',
            'initial_money.max' => 'Il patrimonio iniziale non può superare 50.000 €.',
            'initial_money.numeric' => 'Il patrimonio deve essere un numero valido.',
        ];
    }

    /**
     * Prepara i dati prima della validazione
     * Converte initial_money in money per il database
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Pulisce il nome rimuovendo spazi extra
            'name' => $this->has('name') ? trim($this->name) : null,
            // Pulisce le note
            'notes' => $this->has('notes') ? trim($this->notes) : null,
        ]);
    }

    /**
     * Restituisce i dati validati con i valori di default per il database
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();
        
        return [
            'name' => $validated['name'],
            // Rinomina initial_money -> money per il database
            'money' => $validated['initial_money'] ?? Game::INITIAL_MONEY,
            'notes' => $validated['notes'] ?? null,
            'status' => Game::STATUS_ACTIVE,
            // Aggiungi altri campi di default se necessari
            'user_id' => auth()->id(),
        ];
    }
}