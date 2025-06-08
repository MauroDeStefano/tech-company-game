<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="SalesPerson",
 *     title="Sales Person Model",
 *     description="Rappresenta un commerciale nel gioco",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="game_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Laura Bianchi"),
 *     @OA\Property(property="experience", type="integer", minimum=1, maximum=5, example=3),
 *     @OA\Property(property="monthly_salary", type="number", format="float", example=2000.00),
 *     @OA\Property(property="is_busy", type="boolean", example=false),
 *     @OA\Property(property="is_market_hire", type="boolean", example=false),
 *     @OA\Property(property="hire_date", type="string", format="date-time"),
 *     @OA\Property(property="projects_generated", type="integer", example=8),
 *     @OA\Property(property="total_value_generated", type="number", format="float", example=24000.00),
 *     @OA\Property(property="value_multiplier", type="number", format="float", example=1.2)
 * )
 */
class SalesPerson extends Model
{
    use HasFactory;

    // Costanti per experience levels
    public const EXPERIENCE_TRAINEE = 1;
    public const EXPERIENCE_JUNIOR = 2;
    public const EXPERIENCE_MID = 3;
    public const EXPERIENCE_SENIOR = 4;
    public const EXPERIENCE_MANAGER = 5;

    // Range salary per experience (in EUR)
    public const SALARY_RANGES = [
        self::EXPERIENCE_TRAINEE => [1500, 1800],
        self::EXPERIENCE_JUNIOR => [1800, 2200],
        self::EXPERIENCE_MID => [2200, 2800],
        self::EXPERIENCE_SENIOR => [2800, 3500],
        self::EXPERIENCE_MANAGER => [3500, 4500],
    ];

    /**
     * Colonne mass-assignable
     * CORRETTO: Rimosso specialization per semplicità, usa monthly_salary
     */
    protected $fillable = [
        'game_id',
        'name',
        'experience',
        'monthly_salary',           // ✅ CORRETTO: Allineato con migration corretta
        'is_busy',
        'is_market_hire',
        'hire_date',
        'projects_generated',
        'total_value_generated',
        'value_multiplier',
        'last_project_generated_at',
        'metadata',
    ];

    /**
     * Casting automatico dei tipi
     */
    protected $casts = [
        'experience' => 'integer',
        'monthly_salary' => 'decimal:2',   // ✅ CORRETTO
        'is_busy' => 'boolean',
        'is_market_hire' => 'boolean',
        'hire_date' => 'datetime',
        'projects_generated' => 'integer',
        'total_value_generated' => 'decimal:2',
        'value_multiplier' => 'decimal:2',
        'last_project_generated_at' => 'datetime',
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Valori di default per nuovi record
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (SalesPerson $salesPerson) {
            $salesPerson->hire_date = $salesPerson->hire_date ?? Carbon::now();
            $salesPerson->is_busy = $salesPerson->is_busy ?? false;
            $salesPerson->is_market_hire = $salesPerson->is_market_hire ?? false;
            $salesPerson->projects_generated = $salesPerson->projects_generated ?? 0;
            $salesPerson->total_value_generated = $salesPerson->total_value_generated ?? 0;
            $salesPerson->value_multiplier = $salesPerson->value_multiplier ?? 1.0;
            
            // Auto-calcola salary se non specificata
            if (!$salesPerson->monthly_salary && $salesPerson->experience) {
                $salesPerson->monthly_salary = $salesPerson->calculateSalaryFromExperience();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELAZIONI ELOQUENT
    |--------------------------------------------------------------------------
    */

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function projectGenerations(): HasMany
    {
        return $this->hasMany(ProjectGeneration::class);
    }

    public function completedGenerations(): HasMany
    {
        return $this->hasMany(ProjectGeneration::class)->where('status', 'completed');
    }

    public function currentGeneration()
    {
        return $this->hasOne(ProjectGeneration::class)->where('status', 'in_progress');
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeAvailable($query)
    {
        return $query->where('is_busy', false);
    }

    public function scopeBusy($query)
    {
        return $query->where('is_busy', true);
    }

    public function scopeByExperience($query, int $experience)
    {
        return $query->where('experience', $experience);
    }

    public function scopeMarketHires($query)
    {
        return $query->where('is_market_hire', true);
    }

    /*
    |--------------------------------------------------------------------------
    | BUSINESS LOGIC METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Calcola tempo per generare un progetto
     * Formula: 60 minuti - (experience-1) * 10 minuti
     */
    public function calculateGenerationTime(): int
    {
        $baseTime = 60; // 1 ora base
        $experienceBonus = ($this->experience - 1) * 10; // 10 min per livello
        $finalTime = $baseTime - $experienceBonus;
        
        return max(10, $finalTime); // Minimo 10 minuti
    }

    /**
     * Calcola valore del progetto generato
     * Formula: 1000€ * experience * value_multiplier * random(0.8-1.2)
     */
    public function calculateProjectValue(): float
    {
        $baseValue = 1000 * $this->experience;
        $multipliedValue = $baseValue * $this->value_multiplier;
        $randomFactor = mt_rand(80, 120) / 100; // 0.8 - 1.2
        
        return (float) ($multipliedValue * $randomFactor);
    }

    /**
     * Calcola salary basata sull'experience
     */
    public function calculateSalaryFromExperience(): float
    {
        $range = self::SALARY_RANGES[$this->experience] ?? [1800, 2200];
        return (float) rand($range[0], $range[1]);
    }

    /**
     * Calcola efficiency rating
     */
    public function getEfficiencyRating(): float
    {
        $daysWorking = $this->hire_date->diffInDays(Carbon::now());
        
        if ($daysWorking < 1) {
            return 0.0;
        }
        
        // Target: 0.3 progetti al giorno = 100% efficiency per commerciali
        $projectsPerDay = $this->projects_generated / $daysWorking;
        
        return min(100, ($projectsPerDay / 0.3) * 100);
    }

    /**
     * Valore medio per progetto
     */
    public function getAverageProjectValue(): float
    {
        if ($this->projects_generated === 0) {
            return 0.0;
        }
        
        return $this->total_value_generated / $this->projects_generated;
    }

    /**
     * Conversion rate (tasso di conversione)
     * Simula il tasso di successo del commerciale
     */
    public function getConversionRate(): float
    {
        // Tasso base per experience
        $baseRates = [
            self::EXPERIENCE_TRAINEE => 0.3,   // 30%
            self::EXPERIENCE_JUNIOR => 0.4,    // 40%
            self::EXPERIENCE_MID => 0.5,       // 50%
            self::EXPERIENCE_SENIOR => 0.6,    // 60%
            self::EXPERIENCE_MANAGER => 0.65,  // 65%
        ];
        
        $baseRate = $baseRates[$this->experience] ?? 0.5;
        
        // Bonus per value_multiplier alto
        $multiplierBonus = ($this->value_multiplier - 1.0) * 0.1;
        
        return min(1.0, $baseRate + $multiplierBonus);
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS METHODS
    |--------------------------------------------------------------------------
    */

    public function isAvailable(): bool
    {
        return !$this->is_busy;
    }

    public function isBusy(): bool
    {
        return $this->is_busy;
    }

    public function startGeneration(): bool
    {
        if ($this->is_busy) {
            return false;
        }

        $this->update(['is_busy' => true]);
        return true;
    }

    public function finishGeneration(): bool
    {
        return $this->update([
            'is_busy' => false,
            'last_project_generated_at' => Carbon::now()
        ]);
    }

    public function completeGeneration(float $projectValue): void
    {
        $this->increment('projects_generated');
        $this->increment('total_value_generated', $projectValue);
        $this->update([
            'is_busy' => false,
            'last_project_generated_at' => Carbon::now()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DISPLAY METHODS
    |--------------------------------------------------------------------------
    */

    public function getExperienceName(): string
    {
        return match($this->experience) {
            self::EXPERIENCE_TRAINEE => 'Trainee',
            self::EXPERIENCE_JUNIOR => 'Junior',
            self::EXPERIENCE_MID => 'Mid',
            self::EXPERIENCE_SENIOR => 'Senior',
            self::EXPERIENCE_MANAGER => 'Manager',
            default => 'Unknown',
        };
    }

    public function getExperienceStars(): string
    {
        return str_repeat('⭐', $this->experience);
    }

    public function getExperienceColor(): string
    {
        return match($this->experience) {
            self::EXPERIENCE_TRAINEE => '#6b7280',     // gray-500
            self::EXPERIENCE_JUNIOR => '#3b82f6',     // blue-500
            self::EXPERIENCE_MID => '#10b981',        // green-500
            self::EXPERIENCE_SENIOR => '#f59e0b',     // yellow-500
            self::EXPERIENCE_MANAGER => '#8b5cf6',    // purple-500
            default => '#9ca3af',                     // gray-400
        };
    }

    /**
     * METODI GIÀ PRESENTI NEL MODEL ORIGINALE:
     * - startGeneration(): bool
     * - finishGeneration(): bool  
     * - completeGeneration(Project $project): void
     * 
     * Non ridichiarare questi metodi per evitare errori di duplicazione!
     */

    /*
    |--------------------------------------------------------------------------
    | FACTORY METHODS (SOLO QUESTI SONO NUOVI)
    |--------------------------------------------------------------------------
    */

    public static function createFromMarket(Game $game, array $attributes = []): self
    {
        $defaults = [
            'game_id' => $game->id,
            'name' => fake()->name(),
            'experience' => rand(1, 5),
            'is_busy' => false,
            'is_market_hire' => true,
        ];
        
        return static::create(array_merge($defaults, $attributes));
    }

    public static function generateMarketSalesPeople(int $count = 5): array
    {
        $salesPeople = [];
        
        for ($i = 0; $i < $count; $i++) {
            $experience = fake()->randomElement([1, 2, 2, 3, 3, 4, 5]);
            
            $salesPeople[] = [
                'name' => fake()->name(),
                'experience' => $experience,
                'monthly_salary' => static::calculateMarketSalary($experience),
                'value_multiplier' => fake()->randomFloat(2, 1.0, 1.3),
                'is_busy' => false,
                'is_market_hire' => false,
            ];
        }
        
        return $salesPeople;
    }

    public static function calculateMarketSalary(int $experience): float
    {
        $range = self::SALARY_RANGES[$experience] ?? [1800, 2200];
        return (float) rand($range[0], $range[1]);
    }

    /*
    |--------------------------------------------------------------------------
    | SERIALIZATION
    |--------------------------------------------------------------------------
    */

    public function toArray(): array
    {
        $array = parent::toArray();
        
        // Aggiungi campi calcolati
        $array['experience_name'] = $this->getExperienceName();
        $array['experience_stars'] = $this->getExperienceStars();
        $array['efficiency_rating'] = $this->getEfficiencyRating();
        $array['average_project_value'] = $this->getAverageProjectValue();
        $array['conversion_rate'] = $this->getConversionRate();
        $array['is_available'] = $this->isAvailable();
        
        return $array;
    }
}