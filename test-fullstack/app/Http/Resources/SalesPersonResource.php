<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * SalesPerson API Resource
 * 
 * Trasforma il model SalesPerson in una struttura JSON ottimizzata per l'API.
 * CORRETTO: Rimossa specialization per semplicità, usato monthly_salary
 */
class SalesPersonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            
            // Informazioni experience strutturate
            'experience' => [
                'level' => $this->experience,
                'name' => $this->getExperienceName(),
                'stars' => $this->getExperienceStars(),
                'color' => $this->getExperienceColor(),
            ],
            
            // RIMOSSA specialization per semplicità
            
            // Informazioni economiche (CORRETTO: usa monthly_salary)
            'salary' => [
                'monthly' => $this->monthly_salary,
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
                'projects_generated' => $this->projects_generated,
                'total_value_generated' => $this->total_value_generated,
                'efficiency_rating' => round($this->getEfficiencyRating(), 1),
                'average_project_value' => round($this->getAverageProjectValue(), 2),
                'conversion_rate' => round($this->getConversionRate(), 1),
            ],
            
            // Capacità di generazione
            'generation_capabilities' => [
                'estimated_generation_time' => $this->calculateGenerationTime(),
                'estimated_project_value' => round($this->calculateProjectValue(), 2),
                'value_multiplier' => $this->value_multiplier,
            ],
            
            // Generazione corrente (se esiste)
            'current_generation' => $this->whenLoaded('currentGeneration', function () {
                return $this->currentGeneration ? new ProjectGenerationResource($this->currentGeneration) : null;
            }),
            
            // Date importanti
            'dates' => [
                'hire_date' => $this->hire_date->toISOString(),
                'last_project_generated_at' => $this->when($this->last_project_generated_at, 
                    $this->last_project_generated_at?->toISOString()
                ),
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
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'resource_type' => 'sales_person',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}