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
 *     @OA\Property(property="specialization", type="string", example="enterprise", nullable=true),
 *     @OA\Property(property="hire_date", type="string", format="date-time"),
 *     @OA\Property(property="projects_generated", type="integer", example=8),
 *     @OA\Property(property="total_value_generated", type="number", format="float", example=24000.00)
 * )
 */
class SalesPerson extends Model
{
    use HasFactory;
    public const EXPERIENCE_TRAINEE = 1;
    public const EXPERIENCE_JUNIOR = 2;
    public const EXPERIENCE_MID = 3;
    public const EXPERIENCE_SENIOR = 4;
    public const EXPERIENCE_MANAGER = 5;

    public const SPECIALIZATION_STARTUP = 'startup';
    public const SPECIALIZATION_SME = 'sme'; 
    public const SPECIALIZATION_ENTERPRISE = 'enterprise';
    public const SPECIALIZATION_ECOMMERCE = 'ecommerce';
    public const SPECIALIZATION_CONSULTING = 'consulting';

    public const SALARY_RANGES = [
        self::EXPERIENCE_TRAINEE => [1500, 1800],
        self::EXPERIENCE_JUNIOR => [1800, 2200],
        self::EXPERIENCE_MID => [2200, 2800],
        self::EXPERIENCE_SENIOR => [2800, 3500],
        self::EXPERIENCE_MANAGER => [3500, 4500],
    ];

    protected $fillable = [
        'game_id',
        'name',
        'experience',
        'monthly_salary',
        'is_busy',
        'specialization',
        'hire_date',
        'projects_generated',
        'total_value_generated',
    ];

    protected $casts = [
        'experience' => 'integer',
        'monthly_salary' => 'decimal:2',
        'is_busy' => 'boolean',
        'hire_date' => 'datetime',
        'projects_generated' => 'integer',
        'total_value_generated' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (SalesPerson $salesPerson) {
            $salesPerson->hire_date = $salesPerson->hire_date ?? Carbon::now();
            $salesPerson->is_busy = $salesPerson->is_busy ?? false;
            $salesPerson->projects_generated = $salesPerson->projects_generated ?? 0;
            $salesPerson->total_value_generated = $salesPerson->total_value_generated ?? 0;
            
            if (!$salesPerson->monthly_salary && $salesPerson->experience) {
                $salesPerson->monthly_salary = $salesPerson->calculateSalaryFromExperience();
            }
        });
    }

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

    public function scopeBySpecialization($query, string $specialization)
    {
        return $query->where('specialization', $specialization);
    }

    public function calculateGenerationTime(): int
    {
        $baseTime = 60;
        $experienceBonus = ($this->experience - 1) * 10; 
        $finalTime = $baseTime - $experienceBonus;
        
        return max(10, $finalTime); 
    }

    /**
     * Calcola il valore del progetto generato
     * Formula: 1000€ * experience * random(0.8-1.2)
     */
    public function calculateProjectValue(): float
    {
        $baseValue = 1000 * $this->experience;
        $randomMultiplier = mt_rand(80, 120) / 100; // 0.8 - 1.2
        
        return (float) ($baseValue * $randomMultiplier);
    }

    public function calculateSalaryFromExperience(): float
    {
        $range = self::SALARY_RANGES[$this->experience] ?? [1800, 2200];
        return (float) rand($range[0], $range[1]);
    }

    public function getEfficiencyRating(): float
    {
        $daysWorking = $this->hire_date->diffInDays(Carbon::now());
        
        if ($daysWorking < 1) {
            return 0.0;
        }
        
        $projectsPerDay = $this->projects_generated / $daysWorking;
        
        return min(100, ($projectsPerDay / 0.3) * 100);
    }

    public function getAverageProjectValue(): float
    {
        if ($this->projects_generated === 0) {
            return 0.0;
        }
        
        return $this->total_value_generated / $this->projects_generated;
    }

}