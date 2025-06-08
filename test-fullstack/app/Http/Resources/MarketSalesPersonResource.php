<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketSalesPersonResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name ?? $this['name'],
            'experience' => [
                'level' => $this->experience ?? $this['experience'],
                'name' => $this->getExperienceName ? $this->getExperienceName() : $this->getExperienceNameStatic($this['experience']),
                'stars' => str_repeat('⭐', $this->experience ?? $this['experience']),
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
                'amount' => $this->monthly_salary ?? $this['monthly_salary'],
                'formatted' => number_format($this->monthly_salary ?? $this['monthly_salary'], 2) . ' €',
            ],
            'generation_capabilities' => [
                'estimated_generation_time' => $this->calculateGenerationTime ? $this->calculateGenerationTime() : (60 - (($this->experience ?? $this['experience']) - 1) * 10),
                'estimated_project_value' => $this->calculateProjectValue ? $this->calculateProjectValue() : (1000 * ($this->experience ?? $this['experience'])),
            ],
        ];
    }


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

    private function getSpecializationNameStatic(?string $specialization): ?string
    {
        return match($specialization) {
            'startup' => 'Startup',
            'sme' => 'PMI',
            'enterprise' => 'Enterprise',
            'ecommerce' => 'E-commerce',
            'consulting' => 'Consulting',
            default => null,
        };
    }
}