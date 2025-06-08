<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketDeveloperResource extends JsonResource
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
            'seniority' => [
                'level' => $this->resource['seniority'] ?? $this->seniority,
                'name' => $this->getSeniorityNameStatic($this->resource['seniority'] ?? $this->seniority),
                'stars' => str_repeat('⭐', $this->resource['seniority'] ?? $this->seniority),
                'color' => $this->getSeniorityColorStatic($this->resource['seniority'] ?? $this->seniority),
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
                'amount' => $this->resource['monthly_salary'] ?? $this->monthly_salary, // Costo = 1 mese di stipendio
                'formatted' => number_format($this->resource['monthly_salary'] ?? $this->monthly_salary, 2) . ' €',
            ],
            'skills' => $this->resource['skills'] ?? $this->skills ?? [],
        ];
    }

    /**
     * Helper statico per seniority name (compatibile con array e oggetti)
     */
    private function getSeniorityNameStatic(int $seniority): string
    {
        return match($seniority) {
            1 => 'Junior',
            2 => 'Junior-Mid', 
            3 => 'Mid',
            4 => 'Senior',
            5 => 'Lead',
            default => 'Unknown',
        };
    }

    /**
     * Helper statico per seniority color
     */
    private function getSeniorityColorStatic(int $seniority): string
    {
        return match($seniority) {
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
            'frontend' => 'Frontend',
            'backend' => 'Backend',
            'fullstack' => 'Full Stack',
            'mobile' => 'Mobile',
            'devops' => 'DevOps',
            default => 'Generica',
        };
    }
}