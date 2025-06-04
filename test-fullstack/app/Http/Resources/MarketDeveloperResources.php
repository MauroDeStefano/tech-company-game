<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketDeveloperResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name ?? $this['name'],
            'seniority' => [
                'level' => $this->seniority ?? $this['seniority'],
                'name' => $this->getSeniorityName ? $this->getSeniorityName() : $this->getSeniorityNameStatic($this['seniority']),
                'stars' => str_repeat('⭐', $this->seniority ?? $this['seniority']),
            ],
            'specialization' => [
                'code' => $this->specialization ?? $this['specialization'],
                'name' => $this->getSpecializationName ? $this->getSpecializationName() : $this->getSpecializationNameStatic($this['specialization']),
            ],
            'salary' => [
                'monthly' => $this->monthly_salary ?? $this['monthly_salary'],
                'formatted' => number_format($this->monthly_salary ?? $this['monthly_salary'], 2) . ' €',
            ],
            'hire_cost' => [
                'amount' => $this->monthly_salary ?? $this['monthly_salary'], // Costo assunzione = 1 mese di stipendio
                'formatted' => number_format($this->monthly_salary ?? $this['monthly_salary'], 2) . ' €',
            ],
        ];
    }

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


    private function getSpecializationNameStatic(?string $specialization): ?string
    {
        return match($specialization) {
            'frontend' => 'Frontend',
            'backend' => 'Backend',
            'fullstack' => 'Full Stack',
            'mobile' => 'Mobile',
            'devops' => 'DevOps',
            default => null,
        };
    }
}