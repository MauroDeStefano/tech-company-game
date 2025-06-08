<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Game;
use App\Models\Developer;
use App\Models\SalesPerson;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Tipi di progetti realistici nel settore IT
        $projectTypes = [
            'Website Aziendale', 'E-commerce Platform', 'App Mobile iOS', 'App Mobile Android',
            'Dashboard Analytics', 'Sistema CRM', 'API REST', 'Portale Cliente',
            'Sistema Gestionale', 'Piattaforma E-learning', 'App Prenotazioni', 'Marketplace',
            'Sistema Fatturazione', 'Portale HR', 'App Delivery', 'Sistema Ticketing',
            'Piattaforma SaaS', 'App Fintech', 'Sistema IoT', 'Portale News'
        ];

        // Tipi di clienti realistici
        $clientTypes = [
            'Startup Tech', 'PMI Manifatturiera', 'Agenzia Marketing', 'Studio Medico',
            'Retailer Fashion', 'Ristorante Gourmet', 'Centro Fitness', 'Azienda Logistica',
            'Studio Legale', 'Società Consulting', 'Brand Cosmetico', 'Hotel Boutique',
            'Scuola Privata', 'ONG Locale', 'Cooperativa', 'Startup Fintech',
            'Azienda Agricola', 'Centro Estetico', 'Negozio Elettronica', 'Clinica Veterinaria'
        ];

        // Descrizioni realistiche per settore
        $descriptions = [
            'Sviluppo completo con design responsivo e integrazione CMS',
            'Piattaforma e-commerce con sistema di pagamenti e gestione inventario',
            'Applicazione mobile nativa con UI/UX ottimizzata e notifiche push',
            'Dashboard con analytics avanzati e visualizzazione dati real-time',
            'Sistema gestionale completo con moduli personalizzati',
            'API RESTful con documentazione completa e autenticazione OAuth',
            'Portale clienti con area riservata e sistema ticket',
            'Marketplace B2B con sistema rating e review integrato',
            'App mobile cross-platform con geolocalizzazione',
            'Piattaforma SaaS multi-tenant con scalabilità cloud'
        ];

        // Complessità pesata (più progetti mid-level nel mondo reale)
        $complexityWeights = [
            Project::COMPLEXITY_TRIVIAL => 10,  // 10%
            Project::COMPLEXITY_EASY => 25,     // 25%
            Project::COMPLEXITY_MEDIUM => 40,   // 40%
            Project::COMPLEXITY_HARD => 20,     // 20%
            Project::COMPLEXITY_EXPERT => 5,    // 5%
        ];

        $complexity = $this->faker->randomElement(
            array_merge(...array_map(
                fn($level, $weight) => array_fill(0, $weight, $level),
                array_keys($complexityWeights),
                $complexityWeights
            ))
        );

        // Genera nome progetto realistico
        $projectType = $this->faker->randomElement($projectTypes);
        $clientType = $this->faker->randomElement($clientTypes);
        $projectName = "{$projectType} per {$clientType}";

        // Calcola valore basato su complessità con variazione realistica
        $valueRange = Project::VALUE_RANGES[$complexity];
        $baseValue = $this->faker->randomFloat(2, $valueRange[0], $valueRange[1]);
        
        // Aggiunge variazione per specializzazione del progetto
        $specialtyMultiplier = $this->getSpecialtyMultiplier($projectType);
        $finalValue = $baseValue * $specialtyMultiplier;

        return [
            'game_id' => Game::factory(),
            'developer_id' => null, // La maggior parte dei progetti inizia non assegnati
            // NOTA: Dovremmo usare generated_by_sales_person_id secondo la migration
            'generated_by_sales_person_id' => $this->faker->boolean(80) ? SalesPerson::factory() : null,
            'name' => $projectName,
            'description' => $this->faker->randomElement($descriptions),
            'complexity' => $complexity,
            'value' => $finalValue,
            'status' => Project::STATUS_PENDING, // Default status
            'difficulty_multiplier' => $this->generateDifficultyMultiplier($complexity),
            'rush_bonus' => 0, // Default nessun bonus rush
        ];
    }

    /**
     * Genera moltiplicatore per specialty del progetto
     */
    private function getSpecialtyMultiplier(string $projectType): float
    {
        $multipliers = [
            'API REST' => 1.2,
            'Sistema IoT' => 1.4,
            'Piattaforma SaaS' => 1.3,
            'App Fintech' => 1.5,
            'Dashboard Analytics' => 1.2,
            'Marketplace' => 1.3,
            'Sistema CRM' => 1.1,
            'E-commerce Platform' => 1.2,
            'App Mobile iOS' => 1.1,
            'App Mobile Android' => 1.1,
        ];
        
        return $multipliers[$projectType] ?? 1.0;
    }

    /**
     * Genera difficulty multiplier basato su complessità
     */
    private function generateDifficultyMultiplier(int $complexity): float
    {
        $baseMultipliers = [
            Project::COMPLEXITY_TRIVIAL => [0.8, 1.0],
            Project::COMPLEXITY_EASY => [0.9, 1.1],
            Project::COMPLEXITY_MEDIUM => [1.0, 1.2],
            Project::COMPLEXITY_HARD => [1.1, 1.4],
            Project::COMPLEXITY_EXPERT => [1.3, 1.6],
        ];
        
        $range = $baseMultipliers[$complexity] ?? [1.0, 1.2];
        return $this->faker->randomFloat(2, $range[0], $range[1]);
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS STATES
    |--------------------------------------------------------------------------
    */

    /**
     * Progetto in attesa (default)
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Project::STATUS_PENDING,
            'developer_id' => null,
            'assigned_at' => null,
            'started_at' => null,
            'estimated_completion_at' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Progetto in corso
     */
    public function inProgress(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = $this->faker->dateTimeBetween('-2 days', '-1 hour');
            $estimatedDuration = $attributes['complexity'] * $this->faker->numberBetween(25, 35); // Variazione sui 30 min base
            
            return [
                'status' => Project::STATUS_IN_PROGRESS,
                'developer_id' => Developer::factory(),
                'assigned_at' => $startedAt,
                'started_at' => $startedAt,
                'estimated_completion_at' => Carbon::parse($startedAt)->addMinutes($estimatedDuration),
                'completed_at' => null,
                'estimated_duration_minutes' => $estimatedDuration,
            ];
        });
    }

    /**
     * Progetto completato
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = $this->faker->dateTimeBetween('-1 week', '-1 day');
            $estimatedDuration = $attributes['complexity'] * 30;
            
            // Variazione realistica sui tempi effettivi
            $actualDuration = $estimatedDuration * $this->faker->randomFloat(2, 0.8, 1.3);
            $completedAt = Carbon::parse($startedAt)->addMinutes($actualDuration);
            
            return [
                'status' => Project::STATUS_COMPLETED,
                'developer_id' => Developer::factory(),
                'assigned_at' => $startedAt,
                'started_at' => $startedAt,
                'estimated_completion_at' => Carbon::parse($startedAt)->addMinutes($estimatedDuration),
                'completed_at' => $completedAt,
                'estimated_duration_minutes' => $estimatedDuration,
                'actual_duration_minutes' => (int) $actualDuration,
                'rush_bonus' => $actualDuration < $estimatedDuration ? $this->faker->randomFloat(2, 0.1, 0.2) : 0,
            ];
        });
    }

    /**
     * Progetto cancellato/fallito
     */
    public function cancelled(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = $this->faker->dateTimeBetween('-1 week', '-1 day');
            $estimatedDuration = $attributes['complexity'] * 30;
            $failedAt = Carbon::parse($startedAt)->addMinutes($this->faker->numberBetween(10, $estimatedDuration));
            
            $reasons = [
                'Requisiti cambiati dal cliente',
                'Budget insufficiente',
                'Problemi tecnici imprevisti',
                'Cliente non raggiungibile',
                'Conflitto di priorità interne',
                'Tecnologie obsolete richieste'
            ];
            
            return [
                'status' => Project::STATUS_CANCELLED,
                'developer_id' => $this->faker->boolean(70) ? Developer::factory() : null,
                'assigned_at' => $this->faker->boolean(70) ? $startedAt : null,
                'started_at' => $this->faker->boolean(70) ? $startedAt : null,
                'estimated_completion_at' => $this->faker->boolean(70) ? Carbon::parse($startedAt)->addMinutes($estimatedDuration) : null,
                'completed_at' => $failedAt,
                'estimated_duration_minutes' => $this->faker->boolean(70) ? $estimatedDuration : null,
                'actual_duration_minutes' => $this->faker->boolean(70) ? $startedAt->diffInMinutes($failedAt) : null,
                'metadata' => [
                    'cancellation_reason' => $this->faker->randomElement($reasons),
                    'cancelled_by' => $this->faker->randomElement(['client', 'internal', 'technical']),
                ],
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | COMPLEXITY STATES
    |--------------------------------------------------------------------------
    */

    public function trivial(): static
    {
        return $this->state(fn (array $attributes) => [
            'complexity' => Project::COMPLEXITY_TRIVIAL,
            'value' => $this->faker->randomFloat(2, 800, 1200),
            'difficulty_multiplier' => $this->faker->randomFloat(2, 0.8, 1.0),
        ]);
    }

    public function easy(): static
    {
        return $this->state(fn (array $attributes) => [
            'complexity' => Project::COMPLEXITY_EASY,
            'value' => $this->faker->randomFloat(2, 1200, 2000),
            'difficulty_multiplier' => $this->faker->randomFloat(2, 0.9, 1.1),
        ]);
    }

    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'complexity' => Project::COMPLEXITY_MEDIUM,
            'value' => $this->faker->randomFloat(2, 2000, 3500),
            'difficulty_multiplier' => $this->faker->randomFloat(2, 1.0, 1.2),
        ]);
    }

    public function hard(): static
    {
        return $this->state(fn (array $attributes) => [
            'complexity' => Project::COMPLEXITY_HARD,
            'value' => $this->faker->randomFloat(2, 3500, 5500),
            'difficulty_multiplier' => $this->faker->randomFloat(2, 1.1, 1.4),
        ]);
    }

    public function expert(): static
    {
        return $this->state(fn (array $attributes) => [
            'complexity' => Project::COMPLEXITY_EXPERT,
            'value' => $this->faker->randomFloat(2, 5500, 8000),
            'difficulty_multiplier' => $this->faker->randomFloat(2, 1.3, 1.6),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP STATES
    |--------------------------------------------------------------------------
    */

    /**
     * Progetto non assegnato
     */
    public function unassigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'developer_id' => null,
            'status' => Project::STATUS_PENDING,
        ]);
    }

    /**
     * Progetto assegnato a developer specifico
     */
    public function assignedTo(Developer $developer): static
    {
        return $this->state(fn (array $attributes) => [
            'game_id' => $developer->game_id,
            'developer_id' => $developer->id,
        ]);
    }

    /**
     * Progetto generato da sales person specifico
     */
    public function generatedBy(SalesPerson $salesPerson): static
    {
        return $this->state(fn (array $attributes) => [
            'game_id' => $salesPerson->game_id,
            'generated_by_sales_person_id' => $salesPerson->id,
        ]);
    }

    /**
     * Progetto per un gioco specifico
     */
    public function forGame(Game $game): static
    {
        return $this->state(fn (array $attributes) => [
            'game_id' => $game->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SPECIAL STATES
    |--------------------------------------------------------------------------
    */

    /**
     * Progetto con rush bonus (completato velocemente)
     */
    public function withRushBonus(): static
    {
        return $this->state(fn (array $attributes) => [
            'rush_bonus' => $this->faker->randomFloat(2, 0.1, 0.3),
            'status' => Project::STATUS_COMPLETED,
        ]);
    }

    /**
     * Progetto alto valore
     */
    public function highValue(): static
    {
        return $this->state(function (array $attributes) {
            $baseValue = $attributes['value'] ?? 3000;
            return [
                'value' => $baseValue * $this->faker->randomFloat(2, 1.5, 2.0),
                'complexity' => $this->faker->numberBetween(3, 5), // Almeno medium
            ];
        });
    }

    /**
     * Progetto urgente (timeline accelerata)
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty_multiplier' => $this->faker->randomFloat(2, 1.2, 1.5),
            'rush_bonus' => $this->faker->randomFloat(2, 0.15, 0.25),
            'metadata' => [
                'urgency_level' => 'high',
                'deadline_reason' => $this->faker->randomElement([
                    'Launch event deadline',
                    'Competitor pressure',
                    'Regulatory compliance',
                    'Customer deadline'
                ]),
            ],
        ]);
    }

    /**
     * Progetto long-term (valore alto, complessità alta)
     */
    public function longTerm(): static
    {
        return $this->state(fn (array $attributes) => [
            'complexity' => $this->faker->numberBetween(4, 5),
            'value' => $this->faker->randomFloat(2, 5000, 10000),
            'difficulty_multiplier' => $this->faker->randomFloat(2, 1.3, 1.8),
            'metadata' => [
                'project_duration' => 'long_term',
                'phases' => $this->faker->numberBetween(3, 6),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UTILITY METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Progetto con timeline personalizzata
     */
    public function withTimeline(Carbon $startDate, int $durationMinutes): static
    {
        return $this->state(fn (array $attributes) => [
            'started_at' => $startDate,
            'estimated_completion_at' => $startDate->copy()->addMinutes($durationMinutes),
            'estimated_duration_minutes' => $durationMinutes,
        ]);
    }

    /**
     * Progetto con valore personalizzato
     */
    public function withValue(float $value): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $value,
        ]);
    }

    /**
     * Progetto con metadata personalizzati
     */
    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], $metadata),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURATION
    |--------------------------------------------------------------------------
    */

    /**
     * After creating: aggiusta le relazioni se necessario
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Project $project) {
            // Se il progetto è in progress ma il developer non è busy, correggi
            if ($project->isInProgress() && $project->developer && $project->developer->isAvailable()) {
                $project->developer->update(['is_busy' => true]);
            }
            
            // Se il progetto è completed, assicurati che il developer sia libero
            if ($project->isCompleted() && $project->developer && $project->developer->isBusy()) {
                $project->developer->update(['is_busy' => false]);
            }
        });
    }
}