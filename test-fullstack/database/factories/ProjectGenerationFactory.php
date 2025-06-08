<?php

namespace Database\Factories;

use App\Models\ProjectGeneration;
use App\Models\Game;
use App\Models\SalesPerson;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectGeneration>
 */
class ProjectGenerationFactory extends Factory
{
    protected $model = ProjectGeneration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Valori target realistici per progetti generati
        $targetComplexities = [1, 2, 2, 3, 3, 3, 4, 5]; // Pesato verso complexity medie
        $targetComplexity = $this->faker->randomElement($targetComplexities);
        
        // Calcola valore target basato su complexity
        $valueRanges = [
            1 => [800, 1200],
            2 => [1200, 2000], 
            3 => [2000, 3500],
            4 => [3500, 5500],
            5 => [5500, 8000],
        ];
        
        $valueRange = $valueRanges[$targetComplexity];
        $targetValue = $this->faker->randomFloat(2, $valueRange[0], $valueRange[1]);

        // Nomi target realistici
        $targetNames = [
            'Website Corporate', 'E-commerce Startup', 'App Mobile Delivery',
            'Dashboard Vendite', 'Sistema CRM', 'Portale B2B', 'App Prenotazioni',
            'Piattaforma SaaS', 'Sistema Gestionale', 'API Microservizi',
            'App Fintech', 'Marketplace Locale', 'Portale HR', 'Sistema Ticketing'
        ];

        // Parametri di generazione realistici
        $generationParams = [
            'market_research' => $this->faker->boolean(70),
            'client_meetings' => $this->faker->numberBetween(1, 3),
            'proposal_iterations' => $this->faker->numberBetween(1, 2),
            'competitive_analysis' => $this->faker->boolean(60),
            'technical_assessment' => $this->faker->boolean(80),
        ];

        $startedAt = Carbon::now()->subMinutes($this->faker->numberBetween(5, 180));
        $estimatedDuration = $this->faker->numberBetween(30, 120); // 30-120 minuti per generare

        return [
            'game_id' => Game::factory(),
            'sales_person_id' => SalesPerson::factory(),
            'generated_project_id' => null, // La maggior parte non ha ancora generato il progetto
            'status' => ProjectGeneration::STATUS_IN_PROGRESS,
            'started_at' => $startedAt,
            'estimated_completion_at' => $startedAt->copy()->addMinutes($estimatedDuration),
            'completed_at' => null,
            'estimated_duration_minutes' => $estimatedDuration,
            'target_complexity' => $targetComplexity,
            'target_value' => $targetValue,
            'target_name' => $this->faker->randomElement($targetNames),
            'experience_multiplier' => $this->faker->randomFloat(2, 0.8, 1.5),
            'market_conditions' => $this->faker->randomFloat(2, 0.9, 1.2),
            'generation_parameters' => $generationParams,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS STATES
    |--------------------------------------------------------------------------
    */

    /**
     * Generazione per gioco specifico
     */
    public function forGame(Game $game): static
    {
        return $this->state(fn (array $attributes) => [
            'game_id' => $game->id,
        ]);
    }

    /**
     * Generazione che ha già prodotto un progetto
     */
    public function withGeneratedProject(Project $project): static
    {
        return $this->state(fn (array $attributes) => [
            'game_id' => $project->game_id,
            'generated_project_id' => $project->id,
            'status' => ProjectGeneration::STATUS_COMPLETED,
            'target_complexity' => $project->complexity,
            'target_value' => $project->value,
            'target_name' => $project->name,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MARKET CONDITIONS STATES
    |--------------------------------------------------------------------------
    */

    /**
     * Condizioni di mercato favorevoli
     */
    public function favorableMarket(): static
    {
        return $this->state(fn (array $attributes) => [
            'market_conditions' => $this->faker->randomFloat(2, 1.2, 1.5),
            'experience_multiplier' => $this->faker->randomFloat(2, 1.1, 1.4),
            'target_value' => ($attributes['target_value'] ?? 2000) * $this->faker->randomFloat(2, 1.2, 1.4),
            'generation_parameters' => array_merge(
                $attributes['generation_parameters'] ?? [],
                [
                    'market_trend' => 'bullish',
                    'demand_level' => 'high',
                    'competition_level' => 'low',
                ]
            ),
        ]);
    }

    /**
     * Condizioni di mercato difficili
     */
    public function difficultMarket(): static
    {
        return $this->state(fn (array $attributes) => [
            'market_conditions' => $this->faker->randomFloat(2, 0.7, 0.9),
            'experience_multiplier' => $this->faker->randomFloat(2, 0.8, 1.0),
            'target_value' => ($attributes['target_value'] ?? 2000) * $this->faker->randomFloat(2, 0.7, 0.9),
            'estimated_duration_minutes' => ($attributes['estimated_duration_minutes'] ?? 60) * $this->faker->randomFloat(2, 1.2, 1.6),
            'generation_parameters' => array_merge(
                $attributes['generation_parameters'] ?? [],
                [
                    'market_trend' => 'bearish',
                    'demand_level' => 'low',
                    'competition_level' => 'high',
                    'price_pressure' => true,
                ]
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SPECIAL SCENARIOS
    |--------------------------------------------------------------------------
    */

    /**
     * Cliente esistente (generazione più veloce)
     */
    public function existingClient(): static
    {
        return $this->state(function (array $attributes) {
            $duration = ($attributes['estimated_duration_minutes'] ?? 60) * 0.7; // 30% più veloce
            
            return [
                'estimated_duration_minutes' => (int) $duration,
                'experience_multiplier' => $this->faker->randomFloat(2, 1.2, 1.5),
                'target_value' => ($attributes['target_value'] ?? 2000) * $this->faker->randomFloat(2, 1.1, 1.3),
                'generation_parameters' => array_merge(
                    $attributes['generation_parameters'] ?? [],
                    [
                        'client_type' => 'existing',
                        'relationship_strength' => $this->faker->randomElement(['good', 'excellent']),
                        'trust_level' => 'high',
                        'reduced_documentation' => true,
                    ]
                ),
            ];
        });
    }

    /**
     * Nuovo cliente (processo più lungo)
     */
    public function newClient(): static
    {
        return $this->state(function (array $attributes) {
            $duration = ($attributes['estimated_duration_minutes'] ?? 60) * 1.5; // 50% più lento
            
            return [
                'estimated_duration_minutes' => (int) $duration,
                'experience_multiplier' => $this->faker->randomFloat(2, 0.8, 1.1),
                'generation_parameters' => array_merge(
                    $attributes['generation_parameters'] ?? [],
                    [
                        'client_type' => 'new',
                        'discovery_meetings' => $this->faker->numberBetween(2, 4),
                        'reference_checks' => true,
                        'detailed_proposal' => true,
                        'risk_assessment' => true,
                    ]
                ),
            ];
        });
    }

    /**
     * Generazione urgente
     */
    public function urgent(): static
    {
        return $this->state(function (array $attributes) {
            $duration = ($attributes['estimated_duration_minutes'] ?? 60) * 0.6; // 40% più veloce
            
            return [
                'estimated_duration_minutes' => max(20, (int) $duration),
                'target_value' => ($attributes['target_value'] ?? 2000) * $this->faker->randomFloat(2, 1.1, 1.3), // Premium per urgenza
                'generation_parameters' => array_merge(
                    $attributes['generation_parameters'] ?? [],
                    [
                        'urgency_level' => 'high',
                        'deadline_pressure' => true,
                        'fast_track_approval' => true,
                        'simplified_process' => true,
                    ]
                ),
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | UTILITY METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Generazione con timeline personalizzata
     */
    public function withDuration(int $durationMinutes): static
    {
        return $this->state(function (array $attributes) use ($durationMinutes) {
            $startedAt = $attributes['started_at'] ?? Carbon::now()->subMinutes($this->faker->numberBetween(5, 60));
            
            return [
                'estimated_duration_minutes' => $durationMinutes,
                'estimated_completion_at' => Carbon::parse($startedAt)->addMinutes($durationMinutes),
            ];
        });
    }

    /**
     * Generazione con parametri personalizzati
     */
    public function withParameters(array $parameters): static
    {
        return $this->state(fn (array $attributes) => [
            'generation_parameters' => array_merge(
                $attributes['generation_parameters'] ?? [],
                $parameters
            ),
        ]);
    }

    /**
     * Generazione con target specifico
     */
    public function withTarget(int $complexity, float $value, string $name = null): static
    {
        return $this->state(fn (array $attributes) => [
            'target_complexity' => $complexity,
            'target_value' => $value,
            'target_name' => $name ?? $attributes['target_name'] ?? 'Progetto Custom',
        ]);
    }

    /**
     * Generazione quasi completata (pronta per il completamento)
     */
    public function nearCompletion(): static
    {
        return $this->state(function (array $attributes) {
            $duration = $this->faker->numberBetween(45, 90);
            $startedAt = Carbon::now()->subMinutes($duration - $this->faker->numberBetween(1, 5)); // 1-5 minuti rimanenti
            
            return [
                'status' => ProjectGeneration::STATUS_IN_PROGRESS,
                'started_at' => $startedAt,
                'estimated_duration_minutes' => $duration,
                'estimated_completion_at' => $startedAt->copy()->addMinutes($duration),
                'completed_at' => null,
                'generated_project_id' => null,
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURATION
    |--------------------------------------------------------------------------
    */

    /**
     * After creating: aggiorna lo stato del sales person se necessario
     */
    public function configure(): static
    {
        return $this->afterCreating(function (ProjectGeneration $generation) {
            // Se la generazione è in progress, assicurati che il sales person sia busy
            if ($generation->isInProgress() && $generation->salesPerson && $generation->salesPerson->isAvailable()) {
                $generation->salesPerson->update(['is_busy' => true]);
            }
            
            // Se la generazione è completata/cancellata, assicurati che il sales person sia libero
            if (($generation->isCompleted() || $generation->isCancelled()) && 
                $generation->salesPerson && $generation->salesPerson->isBusy()) {
                $generation->salesPerson->update(['is_busy' => false]);
            }

            // Se la generazione è completata e ha un progetto generato, aggiorna le statistiche del sales person
            if ($generation->isCompleted() && $generation->generated_project_id && $generation->salesPerson) {
                $salesPerson = $generation->salesPerson;
                $project = $generation->actualProject;
                
                if ($project) {
                    $salesPerson->increment('projects_generated');
                    $salesPerson->increment('total_value_generated', $project->value);
                    $salesPerson->update(['last_project_generated_at' => $generation->completed_at]);
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | BATCH CREATION HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Crea generazioni in vari stadi per testing
     */
    public function mixedStatuses(): static
    {
        return $this->state(function (array $attributes) {
            $statuses = [
                ['status' => ProjectGeneration::STATUS_IN_PROGRESS, 'weight' => 40],
                ['status' => ProjectGeneration::STATUS_COMPLETED, 'weight' => 50],
                ['status' => ProjectGeneration::STATUS_CANCELLED, 'weight' => 10],
            ];
            
            $randomChoice = $this->faker->randomElement(
                array_merge(...array_map(
                    fn($item) => array_fill(0, $item['weight'], $item['status']),
                    $statuses
                ))
            );
            
            return match($randomChoice) {
                ProjectGeneration::STATUS_IN_PROGRESS => $this->inProgress()->definition(),
                ProjectGeneration::STATUS_COMPLETED => $this->completed()->definition(),
                ProjectGeneration::STATUS_CANCELLED => $this->cancelled()->definition(),
                default => [],
            };
        });
    }

    /**
     * Crea generazioni per scenari di testing realistici
     */
    public function realistic(): static
    {
        return $this->state(function (array $attributes) {
            // Scenarios realistici con pesi
            $scenarios = [
                'quick_existing_client' => 30,
                'standard_new_client' => 40,
                'high_value_complex' => 15,
                'urgent_small' => 10,
                'difficult_market' => 5,
            ];
            
            $scenario = $this->faker->randomElement(
                array_merge(...array_map(
                    fn($scenario, $weight) => array_fill(0, $weight, $scenario),
                    array_keys($scenarios),
                    $scenarios
                ))
            );
            
            return match($scenario) {
                'quick_existing_client' => $this->existingClient()->quickGeneration()->definition(),
                'standard_new_client' => $this->newClient()->definition(),
                'high_value_complex' => $this->highValue()->definition(),
                'urgent_small' => $this->urgent()->lowValue()->definition(),
                'difficult_market' => $this->difficultMarket()->definition(),
                default => [],
            };
        });
    }
  
    public function inProgress(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = Carbon::now()->subMinutes($this->faker->numberBetween(10, 300));
            $duration = $this->faker->numberBetween(45, 120);
            
            return [
                'status' => ProjectGeneration::STATUS_IN_PROGRESS,
                'started_at' => $startedAt,
                'estimated_completion_at' => $startedAt->copy()->addMinutes($duration),
                'completed_at' => null,
                'generated_project_id' => null,
                'actual_duration_minutes' => null,
            ];
        });
    }

    /**
     * Generazione completata
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = $this->faker->dateTimeBetween('-1 week', '-1 hour');
            $estimatedDuration = $this->faker->numberBetween(45, 120);
            
            // Variazione realistica sui tempi effettivi
            $actualDuration = $estimatedDuration * $this->faker->randomFloat(2, 0.7, 1.4);
            $completedAt = Carbon::parse($startedAt)->addMinutes($actualDuration);
            
            return [
                'status' => ProjectGeneration::STATUS_COMPLETED,
                'started_at' => $startedAt,
                'estimated_completion_at' => Carbon::parse($startedAt)->addMinutes($estimatedDuration),
                'completed_at' => $completedAt,
                'estimated_duration_minutes' => $estimatedDuration,
                'actual_duration_minutes' => (int) $actualDuration,
                'generated_project_id' => Project::factory(), // Progetto creato
            ];
        });
    }

    /**
     * Generazione cancellata
     */
    public function cancelled(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = $this->faker->dateTimeBetween('-1 week', '-1 hour');
            $estimatedDuration = $this->faker->numberBetween(45, 120);
            $cancelledAt = Carbon::parse($startedAt)->addMinutes($this->faker->numberBetween(10, $estimatedDuration));
            
            $cancellationReasons = [
                'Cliente non interessato',
                'Budget insufficiente del cliente',
                'Requisiti fuori scope',
                'Tempi non compatibili',
                'Competitor ha vinto',
                'Progetto interno prioritario'
            ];
            
            return [
                'status' => ProjectGeneration::STATUS_CANCELLED,
                'started_at' => $startedAt,
                'estimated_completion_at' => Carbon::parse($startedAt)->addMinutes($estimatedDuration),
                'completed_at' => $cancelledAt,
                'estimated_duration_minutes' => $estimatedDuration,
                'actual_duration_minutes' => $startedAt->diffInMinutes($cancelledAt),
                'generated_project_id' => null,
                'generation_parameters' => array_merge(
                    $attributes['generation_parameters'] ?? [],
                    [
                        'cancellation_reason' => $this->faker->randomElement($cancellationReasons),
                        'cancelled_stage' => $this->faker->randomElement(['initial', 'negotiation', 'proposal', 'final']),
                    ]
                ),
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | VALUE STATES
    |--------------------------------------------------------------------------
    */

    /**
     * Generazione per progetto alto valore
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_complexity' => $this->faker->numberBetween(4, 5),
            'target_value' => $this->faker->randomFloat(2, 5000, 10000),
            'experience_multiplier' => $this->faker->randomFloat(2, 1.2, 1.6),
            'market_conditions' => $this->faker->randomFloat(2, 1.1, 1.3),
            'estimated_duration_minutes' => $this->faker->numberBetween(90, 180), // Più tempo per progetti grandi
        ]);
    }

    /**
     * Generazione per progetto low-budget
     */
    public function lowValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_complexity' => $this->faker->numberBetween(1, 2),
            'target_value' => $this->faker->randomFloat(2, 500, 1200),
            'experience_multiplier' => $this->faker->randomFloat(2, 0.8, 1.1),
            'market_conditions' => $this->faker->randomFloat(2, 0.9, 1.1),
            'estimated_duration_minutes' => $this->faker->numberBetween(30, 60), // Meno tempo per progetti piccoli
        ]);
    }

    /**
     * Generazione veloce (commerciale esperto)
     */
    public function quickGeneration(): static
    {
        return $this->state(function (array $attributes) {
            $duration = $this->faker->numberBetween(20, 45); // Molto veloce
            $startedAt = $attributes['started_at'] ?? Carbon::now()->subMinutes($this->faker->numberBetween(5, 30));
            
            return [
                'estimated_duration_minutes' => $duration,
                'estimated_completion_at' => Carbon::parse($startedAt)->addMinutes($duration),
                'experience_multiplier' => $this->faker->randomFloat(2, 1.3, 1.6),
                'generation_parameters' => array_merge(
                    $attributes['generation_parameters'] ?? [],
                    [
                        'fast_track' => true,
                        'existing_client' => $this->faker->boolean(70),
                        'template_used' => $this->faker->boolean(80),
                    ]
                ),
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP STATES
    |--------------------------------------------------------------------------
    */

    /**
     * Generazione per sales person specifico
     */
    public function forSalesPerson(SalesPerson $salesPerson): static
    {
        return $this->state(function (array $attributes) use ($salesPerson) {
            // Aggiusta parametri basati sull'experience del sales person
            $experienceMultiplier = 0.8 + ($salesPerson->experience * 0.15);
            $duration = max(30, 90 - ($salesPerson->experience * 10));
            
            return [
                'game_id' => $salesPerson->game_id,
                'sales_person_id' => $salesPerson->id,
                'experience_multiplier' => $experienceMultiplier,
                'estimated_duration_minutes' => $duration,
            ];
        });
    }
}