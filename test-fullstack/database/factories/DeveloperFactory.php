<?php

namespace Database\Factories;

use App\Models\Developer;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Developer>
 */
class DeveloperFactory extends Factory
{
    protected $model = Developer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Nomi realistici per developers
        $developerNames = [
            'Alessandro Bianchi', 'Giulia Rossi', 'Marco Ferri', 'Francesca Conti',
            'Luca Fontana', 'Sara Romano', 'Andrea Ricci', 'Elena Marino',
            'Davide Greco', 'Chiara Lombardi', 'Matteo Galli', 'Valentina Rizzo',
            'Simone Costa', 'Anna Ferrari', 'Roberto Esposito', 'Laura Barbieri',
            'Federico Martini', 'Ilaria Bruno', 'Stefano Colombo', 'Martina Vitale'
        ];

        // Distribuzione seniority pesata (più realistico)
        $seniorityWeights = [
            Developer::SENIORITY_JUNIOR => 35,      // 35%
            Developer::SENIORITY_JUNIOR_MID => 25,  // 25%
            Developer::SENIORITY_MID => 20,         // 20%
            Developer::SENIORITY_SENIOR => 15,      // 15%
            Developer::SENIORITY_LEAD => 5,         // 5%
        ];

        $seniority = $this->faker->randomElement(
            array_merge(...array_map(
                fn($level, $weight) => array_fill(0, $weight, $level),
                array_keys($seniorityWeights),
                $seniorityWeights
            ))
        );

        // Calcola salary basato su seniority
        $salaryRange = Developer::SALARY_RANGES[$seniority];
        $salary = $this->faker->randomFloat(2, $salaryRange[0], $salaryRange[1]);

        // Date di assunzione realistiche
        $hireDate = $this->faker->dateTimeBetween('-2 years', '-1 week');

        return [
            'game_id' => Game::factory(),
            'name' => $this->faker->randomElement($developerNames),
            'seniority' => $seniority,
            'monthly_salary' => $salary,
            'is_busy' => $this->faker->boolean(20), // 20% occupati
            'is_market_hire' => false,
            'hire_date' => $hireDate,
            'projects_completed' => $this->generateProjectsCompleted($seniority, $hireDate),
            // 'total_value_delivered' => 0, // Calcolato dopo
        ];
    }

    /**
     * Calcola progetti completati realistici
     */
    private function generateProjectsCompleted(int $seniority, $hireDate): int
    {
        $daysWorking = Carbon::parse($hireDate)->diffInDays(now());
        $weeksWorking = max(1, $daysWorking / 7);
        
        // Produttività per seniority
        $productivityRates = [
            Developer::SENIORITY_JUNIOR => 0.3,
            Developer::SENIORITY_JUNIOR_MID => 0.5,
            Developer::SENIORITY_MID => 0.7,
            Developer::SENIORITY_SENIOR => 1.0,
            Developer::SENIORITY_LEAD => 0.8, // Lead fanno meno coding
        ];
        
        $baseProjects = $weeksWorking * ($productivityRates[$seniority] ?? 0.5);
        
        // Variazione ±30%
        $variation = $this->faker->numberBetween(-30, 30) / 100;
        $projects = $baseProjects * (1 + $variation);
        
        return max(0, (int) round($projects));
    }

    /*
    |--------------------------------------------------------------------------
    | STATE METHODS
    |--------------------------------------------------------------------------
    */

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_busy' => false,
        ]);
    }

    public function busy(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_busy' => true,
        ]);
    }

    public function marketHire(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_market_hire' => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SENIORITY METHODS
    |--------------------------------------------------------------------------
    */

    public function junior(): static
    {
        return $this->state(fn (array $attributes) => [
            'seniority' => Developer::SENIORITY_JUNIOR,
            'monthly_salary' => $this->faker->randomFloat(2, 1800, 2200),
            'projects_completed' => $this->faker->numberBetween(0, 5),
        ]);
    }

    public function senior(): static
    {
        return $this->state(fn (array $attributes) => [
            'seniority' => Developer::SENIORITY_SENIOR,
            'monthly_salary' => $this->faker->randomFloat(2, 3500, 4500),
            'projects_completed' => $this->faker->numberBetween(15, 50),
            'hire_date' => $this->faker->dateTimeBetween('-3 years', '-6 months'),
        ]);
    }

    public function lead(): static
    {
        return $this->state(fn (array $attributes) => [
            'seniority' => Developer::SENIORITY_LEAD,
            'monthly_salary' => $this->faker->randomFloat(2, 4500, 6000),
            'projects_completed' => $this->faker->numberBetween(30, 80),
            'hire_date' => $this->faker->dateTimeBetween('-5 years', '-1 year'),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UTILITY METHODS
    |--------------------------------------------------------------------------
    */

    public function newHire(): static
    {
        return $this->state(fn (array $attributes) => [
            'hire_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'projects_completed' => 0,
            // 'total_value_delivered' => 0,
            'is_busy' => false,
        ]);
    }

    public function veteran(): static
    {
        return $this->state(fn (array $attributes) => [
            'hire_date' => $this->faker->dateTimeBetween('-5 years', '-2 years'),
            'seniority' => $this->faker->numberBetween(3, 5),
            'projects_completed' => $this->faker->numberBetween(25, 100),
        ]);
    }

    public function highPerformer(): static
    {
        return $this->state(function (array $attributes) {
            $hireDate = $this->faker->dateTimeBetween('-2 years', '-6 months');
            $extraProjects = $this->faker->numberBetween(10, 30);
            
            return [
                'hire_date' => $hireDate,
                'projects_completed' => $this->generateProjectsCompleted($attributes['seniority'], $hireDate) + $extraProjects,
                'seniority' => max(3, $attributes['seniority']),
            ];
        });
    }

    public function forGame(Game $game): static
    {
        return $this->state(fn (array $attributes) => [
            'game_id' => $game->id,
        ]);
    }

    public function withSalary(float $salary): static
    {
        return $this->state(fn (array $attributes) => [
            'monthly_salary' => $salary,
        ]);
    }

    public function withProjectsCompleted(int $projects): static
    {
        return $this->state(fn (array $attributes) => [
            'projects_completed' => $projects,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURATION
    |--------------------------------------------------------------------------
    */

    public function configure(): static
    {
        return $this->afterCreating(function (Developer $developer) {
            // Calcola total_value_delivered se ha progetti completati
            if ($developer->projects_completed > 0) {
                $avgValuePerProject = match($developer->seniority) {
                    Developer::SENIORITY_JUNIOR => $this->faker->randomFloat(2, 800, 1500),
                    Developer::SENIORITY_JUNIOR_MID => $this->faker->randomFloat(2, 1200, 2000),
                    Developer::SENIORITY_MID => $this->faker->randomFloat(2, 1500, 2500),
                    Developer::SENIORITY_SENIOR => $this->faker->randomFloat(2, 2000, 3500),
                    Developer::SENIORITY_LEAD => $this->faker->randomFloat(2, 2500, 4000),
                    default => 1500,
                };
                
                $totalValue = $developer->projects_completed * $avgValuePerProject;
                // $developer->update(['total_value_delivered' => $totalValue]);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | STARTING TEAM
    |--------------------------------------------------------------------------
    */

    public function startingTeam(): static
    {
        // Team iniziale bilanciato
        $seniorityDistribution = [
            Developer::SENIORITY_JUNIOR,
            Developer::SENIORITY_JUNIOR_MID,
            Developer::SENIORITY_MID,
        ];
        
        return $this->state(fn (array $attributes) => [
            'seniority' => $this->faker->randomElement($seniorityDistribution),
            'hire_date' => now(),
            'projects_completed' => 0,
            // 'total_value_delivered' => 0,
            'is_busy' => false,
            'is_market_hire' => false,
        ]);
    }
}