<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Developer API Resource
 * 
 * Trasforma il model Developer in una struttura JSON ottimizzata per l'API.
 * Segue le best practices Laravel per Resources:
 * - Struttura annidata per dati correlati
 * - Formattazione appropriata dei valori
 * - Campi calcolati esposti in modo consistente
 */
class DeveloperResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * 
     * Questo metodo viene chiamato automaticamente quando il resource
     * viene serializzato in JSON (es. return response()->json($resource))
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            
            // Informazioni seniority strutturate
            'seniority' => [
                'level' => $this->seniority,
                'name' => $this->getSeniorityName(),
                'stars' => $this->getSeniorityStars(),
                'color' => $this->getSeniorityColor(),
            ],
            
            // Rimuoviamo specialization per semplicità
            
            // Informazioni economiche (CORRETTO: usa monthly_salary)
            'salary' => [
                'monthly' => $this->monthly_salary,               // ✅ CORRETTO
                'formatted' => number_format($this->monthly_salary, 2) . ' €',
                'currency' => 'EUR',
            ],
            
            // Status operativo
            'status' => [
                'is_busy' => $this->is_busy,
                'is_available' => $this->isAvailable(),
                'is_market_hire' => $this->is_market_hire,
            ],
            
            // Statistiche performance
            'statistics' => [
                'projects_completed' => $this->projects_completed,
                'total_value_delivered' => $this->total_value_delivered,
                'efficiency_rating' => round($this->getEfficiencyRating(), 1),
                'average_project_value' => round($this->getAverageProjectValue(), 2),
            ],
            
            // Progetto corrente (se esiste)
            // whenLoaded() carica la relazione solo se già presente per evitare N+1 queries
            'current_project' => $this->whenLoaded('currentProject', function () {
                return $this->currentProject ? new ProjectResource($this->currentProject) : null;
            }),
            
            // Date importanti
            'dates' => [
                'hire_date' => $this->hire_date->toISOString(),
                'created_at' => $this->created_at->toISOString(),
                'updated_at' => $this->updated_at->toISOString(),
            ],
            
            // Metadati aggiuntivi (se esistono)
            'metadata' => $this->when($this->metadata, $this->metadata),
        ];
    }
    
    /**
     * Get additional data that should be returned with the resource array.
     * 
     * Questo metodo permette di aggiungere metadati a livello di risorsa
     * senza includerli nei dati principali.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'resource_type' => 'developer',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}