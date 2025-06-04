<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class DeveloperResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'seniority' => [
                'level' => $this->seniority,
                'name' => $this->getSeniorityName(),
                'stars' => $this->getSeniorityStars(),
                'color' => $this->getSeniorityColor(),
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
                'projects_completed' => $this->projects_completed,
                'total_value_delivered' => $this->total_value_delivered,
                'efficiency_rating' => round($this->getEfficiencyRating(), 1),
                'average_project_value' => $this->getAverageProjectValue(),
            ],
            'current_project' => $this->whenLoaded('currentProject', function () {
                return $this->currentProject ? new ProjectResource($this->currentProject) : null;
            }),
            'hire_date' => $this->hire_date->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}