<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketSalesPersonResource extends JsonResource
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
            'id' => $this->resource['id'] ?? $this->id,
            'name' => $this->resource['name'] ?? $this->name,
            'experience' => [
                'level' => $this->resource['experience'] ?? $this->experience,
                'name' => $this->getExperienceNameStatic($this->resource['experience'] ?? $this->experience),
                'stars' => str_repeat('⭐', $this->resource['experience'] ?? $this->experience),
                'color' => $this->getExperienceColorStatic($this->resource['experience'] ?? $this->experience),
            ],
            'specialization' => [
                'code' => $this->resource['specialization'] ?? $this->specialization ?? null,
                'name' => $this->getSpecializationNameStatic($this->resource['specialization'] ?? $this->specialization ?? null),
            ],
            'salary' => [
                'monthly' => $this->resource['monthly_salary'] ?? $this->monthly_salary,
                'formatted' => number_format($this->resource['monthly_salary'] ?? $this->monthly_salary, 2) . ' €',
            ],
            'hire_cost' => [
                'amount' => $this->resource['monthly_salary'] ?? $this->monthly_salary,
                'formatted' => number_format($this->resource['monthly_salary'] ?? $this->monthly_salary, 2) . ' €',
            ],
            'generation_capabilities' => [
                'estimated_generation_time' => $this->resource['estimated_generation_time'] ?? 60 - (($this->resource['experience'] ?? $this->experience) - 1) * 10,
                'estimated_project_value' => $this->resource['estimated_project_value'] ?? 1000 * ($this->resource['experience'] ?? $this->experience),
            ],
        ];
    }

    /**
     * Helper statico per experience name
     */
    private function getExperienceNameStatic(int $experience): string
    {
        return match($experience) {
            1 => 'Trainee',
            2 => 'Junior',
            3 => 'Mid',
            4 => 'Senior',
            5 => 'Manager',
            default => 'Unknown',
        };
    }

    /**
     * Helper statico per experience color
     */
    private function getExperienceColorStatic(int $experience): string
    {
        return match($experience) {
            1 => '#6b7280',     // gray-500
            2 => '#3b82f6',     // blue-500
            3 => '#10b981',     // green-500
            4 => '#f59e0b',     // yellow-500
            5 => '#8b5cf6',     // purple-500
            default => '#9ca3af', // gray-400
        };
    }

    /**
     * Helper statico per specialization name
     */
    private function getSpecializationNameStatic(?string $specialization): ?string
    {
        return match($specialization) {
            'startup' => 'Startup',
            'sme' => 'PMI',
            'enterprise' => 'Enterprise',
            'ecommerce' => 'E-commerce',
            'consulting' => 'Consulting',
            default => 'Generica',
        };
    }
}