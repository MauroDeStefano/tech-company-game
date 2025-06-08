<?php

namespace Database\Factories;

use App\Models\SalesPerson;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalesPerson>
 */
class SalesPersonFactory extends Factory
{
    protected $model = SalesPerson::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Nomi realistici per commerciali (mix di nomi maschili e femminili)
        $salesNames = [
            'Laura Bianchi', 'Marco Rossi', 'Giulia Ferri', 'Alessandro Conti',
            'Francesca Romano', 'Luca Ricci', 'Sara Marino', 'Andrea Greco',
            'Elena Lombardi', 'Davide Galli', 'Chiara Rizzo', 'Matteo Costa',
            'Valentina Ferrari', 'Simone Esposito', 'Anna Barbieri', 'Roberto Martini',
            'Ilaria Bruno', 'Federico Colombo', 'Martina Vitale', 'Stefano De Luca'
        ];

        // Distribuzione experience pesata (commerciali tendono ad essere più senior)
        $experienceWeights = [
            SalesPerson::EXPERIENCE_TRAINEE => 15,   // 15% 
            SalesPerson::EXPERIENCE_JUNIOR => 25,    // 25%
            SalesPerson::EXPERIENCE_MID => 30,       // 30%
            SalesPerson::EXPERIENCE_SENIOR => 25,    // 25%
            SalesPerson::EXPERIENCE_MANAGER => 5,    // 5%
        ];

        $experience = $this->faker->randomElement(
            array_merge(...array_map(
                fn($level, $weight) => array_fill(0, $weight, $level),
                array_keys($experienceWeights),
                $experienceWeights
            ))
        );

        // Calcola salary basato sull'experience
        $salaryRange = SalesPerson::SALARY_RANGES[$experience];
        $salary = $this->faker->randomFloat(2, $salaryRange[0], $salaryRange[1]);

        // Date di assunzione realistiche
        $hireDate = $this->faker->dateTimeBetween('-3 years', '-1 week');

        return [
            'game_id' => Game::factory(),
            'name' => $this->faker->randomElement($salesNames),
            'experience' => $experience,
            'monthly_salary' => $salary,
            'is_busy' => $this->faker->boolean(15), // 15% occupati (meno dei dev)
            'is_market_hire' => false,
            'hire_date' => $hireDate,
            'projects_generated' => $this->generateProjectsGenerated($experience, $hireDate),
            'total_value_generated' => 0, // Calcolato dopo
            'value_multiplier' => $this->generateValueMultiplier($experience),
        ];
    }

    /**
     * Calcola progetti generati realistici basati su experience e tempo
     */
    private function generateProjectsGenerated(int $experience, $hireDate): int
    {
        $daysWorking = Carbon::parse($hireDate)->diffInDays(now());
        $weeksWorking = max(1, $daysWorking / 7);
        
        // Produttività commerciali (progetti per settimana)
        $productivityRates = [
            SalesPerson::EXPERIENCE_TRAINEE => 0.2,   // 1 progetto ogni 5 settimane
            SalesPerson::EXPERIENCE_JUNIOR => 0.3,    // 1 progetto ogni 3 settimane  
            SalesPerson::EXPERIENCE_MID => 0.5,       // 1 progetto ogni 2 settimane
            SalesPerson::EXPERIENCE_SENIOR => 0.7,    // 0.7 progetti a settimana
            SalesPerson::EXPERIENCE_MANAGER => 0.4,   // Manager fanno meno vendita diretta
        ];
        
        $baseProjects = $weeksWorking * ($productivityRates[$experience] ?? 0.3);
        
        // Variazione ±40% (le vendite sono più volatili)
        $variation = $this->faker->numberBetween(-40, 40) / 100;
        $projects = $baseProjects * (1 + $variation);
        
        return max(0, (int) round($projects));
    }

    /**
     * Genera moltiplicatore di valore basato sull'experience
     */
    private function generateValueMultiplier(int $experience): float
    {
        $baseMultipliers = [
            SalesPerson::EXPERIENCE_TRAINEE => [0.8, 1.0],
            SalesPerson::EXPERIENCE_JUNIOR => [0.9, 1.1], 
            SalesPerson::EXPERIENCE_MID => [1.0, 1.2],
            SalesPerson::EXPERIENCE_SENIOR => [1.1, 1.4],
            SalesPerson::EXPERIENCE_MANAGER => [1.2, 1.5],
        ];
        
        $range = $baseMultipliers[$experience] ?? [1.0, 1.2];
        return $this->faker->randomFloat(2, $range[0], $range[1]);
    }

    /*
    |--------------------------------------------------------------------------
    | STATE METHODS  
    |--------------------------------------------------------------------------
    */

    /**
     * Sales person disponibile
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_busy' => false,
        ]);
    }

    /**
     * Sales person occupato
     */
    public function busy(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_busy' => true,
        ]);
    }

    /**
     * Assunto dal mercato
     */
    public function marketHire(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_market_hire' => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | EXPERIENCE LEVELS
    |--------------------------------------------------------------------------
    */

    /**
     * Trainee (stagista/apprendista)
     */
    public function trainee(): static
    {
        return $this->state(fn (array $attributes) => [
            'experience' => SalesPerson::EXPERIENCE_TRAINEE,
            'monthly_salary' => $this->faker->randomFloat(2, 1500, 1800),
            'projects_generated' => $this->faker->numberBetween(0, 3),
            'value_multiplier' => $this->faker->randomFloat(2, 0.8, 1.0),
        ]);
    }

    /**
     * Junior
     */
    public function junior(): static
    {
        return $this->state(fn (array $attributes) => [
            'experience' => SalesPerson::EXPERIENCE_JUNIOR,
            'monthly_salary' => $this->faker->randomFloat(2, 1800, 2200),
            'projects_generated' => $this->faker->numberBetween(2, 8),
            'value_multiplier' => $this->faker->randomFloat(2, 0.9, 1.1),
        ]);
    }

    /**
     * Mid level
     */
    public function mid(): static
    {
        return $this->state(fn (array $attributes) => [
            'experience' => SalesPerson::EXPERIENCE_MID,
            'monthly_salary' => $this->faker->randomFloat(2, 2200, 2800),
            'projects_generated' => $this->faker->numberBetween(5, 15),
            'value_multiplier' => $this->faker->randomFloat(2, 1.0, 1.2),
        ]);
    }

    /**
     * Senior
     */
    public function senior(): static
    {
        return $this->state(fn (array $attributes) => [
            'experience' => SalesPerson::EXPERIENCE_SENIOR,
            'monthly_salary' => $this->faker->randomFloat(2, 2800, 3500),
            'projects_generated' => $this->faker->numberBetween(10, 25),
            'value_multiplier' => $this->faker->randomFloat(2, 1.1, 1.4),
            'hire_date' => $this->faker->dateTimeBetween('-4 years', '-6 months'),
        ]);
    }

    /**
     * Manager 
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'experience' => SalesPerson::EXPERIENCE_MANAGER,
            'monthly_salary' => $this->faker->randomFloat(2, 3500, 4500),
            'projects_generated' => $this->faker->numberBetween(15, 40),
            'value_multiplier' => $this->faker->randomFloat(2, 1.2, 1.5),
            'hire_date' => $this->faker->dateTimeBetween('-6 years', '-1 year'),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UTILITY METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Nuovo assunto
     */
    public function newHire(): static
    {
        return $this->state(fn (array $attributes) => [
            'hire_date' => $this->faker->dateTimeBetween('-2 weeks', 'now'),
            'projects_generated' => 0,
            'total_value_generated' => 0,
            'is_busy' => false,
        ]);
    }

    /**
     * Veterano (molto tempo in azienda)
     */
    public function veteran(): static
    {
        return $this->state(fn (array $attributes) => [
            'hire_date' => $this->faker->dateTimeBetween('-8 years', '-3 years'),
            'experience' => $this->faker->numberBetween(3, 5),
            'projects_generated' => $this->faker->numberBetween(20, 60),
        ]);
    }

    /**
     * Top performer (alta performance)
     */
    public function topPerformer(): static
    {
        return $this->state(function (array $attributes) {
            $hireDate = $this->faker->dateTimeBetween('-3 years', '-6 months');
            $extraProjects = $this->faker->numberBetween(10, 25);
            
            return [
                'hire_date' => $hireDate,
                'projects_generated' => $this->generateProjectsGenerated($attributes['experience'], $hireDate) + $extraProjects,
                'experience' => max(3, $attributes['experience']), // Almeno Mid
                'value_multiplier' => $this->faker->randomFloat(2, 1.2, 1.5),
            ];
        });
    }

    /**
     * Associa a un gioco specifico
     */
    public function forGame(Game $game): static
    {
        return $this->state(fn (array $attributes) => [
            'game_id' => $game->id,
        ]);
    }

    /**
     * Salary personalizzato
     */
    public function withSalary(float $salary): static
    {
        return $this->state(fn (array $attributes) => [
            'monthly_salary' => $salary,
        ]);
    }

    /**
     * Progetti generati specifici
     */
    public function withProjectsGenerated(int $projects): static
    {
        return $this->state(fn (array $attributes) => [
            'projects_generated' => $projects,
        ]);
    }

    /**
     * Value multiplier personalizzato
     */
    public function withValueMultiplier(float $multiplier): static
    {
        return $this->state(fn (array $attributes) => [
            'value_multiplier' => $multiplier,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURATION
    |--------------------------------------------------------------------------
    */

    /**
     * After creating: calcola total_value_generated
     */
    public function configure(): static
    {
        return $this->afterCreating(function (SalesPerson $salesPerson) {
            // Calcola total_value_generated se ha progetti generati
            if ($salesPerson->projects_generated > 0) {
                // Valore medio per progetto basato su experience
                $avgValuePerProject = match($salesPerson->experience) {
                    SalesPerson::EXPERIENCE_TRAINEE => $this->faker->randomFloat(2, 800, 1200),
                    SalesPerson::EXPERIENCE_JUNIOR => $this->faker->randomFloat(2, 1200, 1800),
                    SalesPerson::EXPERIENCE_MID => $this->faker->randomFloat(2, 1500, 2500),
                    SalesPerson::EXPERIENCE_SENIOR => $this->faker->randomFloat(2, 2000, 3500),
                    SalesPerson::EXPERIENCE_MANAGER => $this->faker->randomFloat(2, 2500, 4500),
                    default => 1800,
                };
                
                // Applica il value_multiplier
                $avgValuePerProject *= $salesPerson->value_multiplier;
                
                $totalValue = $salesPerson->projects_generated * $avgValuePerProject;
                $salesPerson->update(['total_value_generated' => $totalValue]);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | STARTING TEAM
    |--------------------------------------------------------------------------
    */

    /**
     * Team commerciali iniziale bilanciato
     */
    public function startingTeam(): static
    {
        // Distribuzione per team iniziale
        $experienceDistribution = [
            SalesPerson::EXPERIENCE_JUNIOR,
            SalesPerson::EXPERIENCE_MID,
            SalesPerson::EXPERIENCE_SENIOR,
        ];
        
        return $this->state(fn (array $attributes) => [
            'experience' => $this->faker->randomElement($experienceDistribution),
            'hire_date' => now(),
            'projects_generated' => 0,
            'total_value_generated' => 0,
            'is_busy' => false,
            'is_market_hire' => false,
        ]);
    }
}