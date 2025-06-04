<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class SalesPersonResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'experience' => [
                'level' => $this->experience,
                'name' => $this->getExperienceName(),
                'stars' => $this->getExperienceStars(),
                'color' => $this->getExperienceColor(),
            ],
            'specialization' => [
                'code' => $this->specialization,
                'name' => $this->getSpecializationName(),
            ],
            'salary' => [
                'monthly' => $this->monthly_salary,
                'formatted' => number_format($this->monthly_salary, 2) . ' €',
            ],
            'status' => [
                'is_busy' => $this->is_busy,
                'is_available' => $this->isAvailable(),
            ],
            'statistics' => [
                'projects_generated' => $this->projects_generated,
                'total_value_generated' => $this->total_value_generated,
                'efficiency_rating' => round($this->getEfficiencyRating(), 1),
                'average_project_value' => $this->getAverageProjectValue(),
                'conversion_rate' => round($this->getConversionRate(), 1),
            ],
            'generation_capabilities' => [
                'estimated_generation_time' => $this->calculateGenerationTime(),
                'estimated_project_value' => $this->calculateProjectValue(),
            ],
            'current_generation' => $this->whenLoaded('currentGeneration', function () {
                return $this->currentGeneration ? new ProjectGenerationResource($this->currentGeneration) : null;
            }),
            'hire_date' => $this->hire_date->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}