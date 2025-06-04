<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="Developer",
 *     title="Developer Model",
 *     description="Rappresenta uno sviluppatore nel gioco",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="game_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Marco Rossi"),
 *     @OA\Property(property="seniority", type="integer", minimum=1, maximum=5, example=3),
 *     @OA\Property(property="monthly_salary", type="number", format="float", example=2500.00),
 *     @OA\Property(property="is_busy", type="boolean", example=false),
 *     @OA\Property(property="specialization", type="string", example="backend", nullable=true),
 *     @OA\Property(property="hire_date", type="string", format="date-time"),
 *     @OA\Property(property="projects_completed", type="integer", example=5),
 *     @OA\Property(property="total_value_delivered", type="number", format="float", example=15000.00)
 * )
 */
class Developer extends Model
{
    use HasFactory;

    public const SENIORITY_JUNIOR = 1;
    public const SENIORITY_JUNIOR_MID = 2;
    public const SENIORITY_MID = 3;
    public const SENIORITY_SENIOR = 4;
    public const SENIORITY_LEAD = 5;

    public const SPECIALIZATION_FRONTEND = 'frontend';
    public const SPECIALIZATION_BACKEND = 'backend';
    public const SPECIALIZATION_FULLSTACK = 'fullstack';
    public const SPECIALIZATION_MOBILE = 'mobile';
    public const SPECIALIZATION_DEVOPS = 'devops';

    public const SALARY_RANGES = [
        self::SENIORITY_JUNIOR => [1800, 2200],
        self::SENIORITY_JUNIOR_MID => [2200, 2800],
        self::SENIORITY_MID => [2800, 3500],
        self::SENIORITY_SENIOR => [3500, 4500],
        self::SENIORITY_LEAD => [4500, 6000],
    ];

    protected $fillable = [
        'game_id',
        'name',
        'seniority',
        'monthly_salary',
        'is_busy',
        'specialization',
        'hire_date',
        'projects_completed',
        'total_value_delivered',
    ];

    protected $casts = [
        'seniority' => 'integer',
        'monthly_salary' => 'decimal:2',
        'is_busy' => 'boolean',
        'hire_date' => 'datetime',
        'projects_completed' => 'integer',
        'total_value_delivered' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Developer $developer) {
            $developer->hire_date = $developer->hire_date ?? Carbon::now();
            $developer->is_busy = $developer->is_busy ?? false;
            $developer->projects_completed = $developer->projects_completed ?? 0;
            $developer->total_value_delivered = $developer->total_value_delivered ?? 0;
            
            if (!$developer->monthly_salary && $developer->seniority) {
                $developer->monthly_salary = $developer->calculateSalaryFromSeniority();
            }
        });
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function completedProjects(): HasMany
    {
        return $this->hasMany(Project::class)->where('status', 'completed');
    }

    public function currentProject()
    {
        return $this->hasOne(Project::class)->where('status', 'in_progress');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_busy', false);
    }

    public function scopeBusy($query)
    {
        return $query->where('is_busy', true);
    }

    public function scopeBySeniority($query, int $seniority)
    {
        return $query->where('seniority', $seniority);
    }

    public function scopeBySpecialization($query, string $specialization)
    {
        return $query->where('specialization', $specialization);
    }

    public function calculateCompletionTime(int $projectComplexity): int
    {
        $baseTime = $projectComplexity * 30; 
        $seniorityBonus = ($this->seniority - 1) * 0.15;
        $finalTime = $baseTime * (1 - $seniorityBonus);
        
        return max(5, (int) $finalTime); 
    }

    public function calculateSalaryFromSeniority(): float
    {
        $range = self::SALARY_RANGES[$this->seniority] ?? [2000, 2500];
        return (float) rand($range[0], $range[1]);
    }

    public function getEfficiencyRating(): float
    {
        $daysWorking = $this->hire_date->diffInDays(Carbon::now());
        
        if ($daysWorking < 1) {
            return 0.0;
        }
        
        $projectsPerDay = $this->projects_completed / $daysWorking;
        
        return min(100, ($projectsPerDay / 0.5) * 100);
    }

    public function getAverageProjectValue(): float
    {
        if ($this->projects_completed === 0) {
            return 0.0;
        }
        
        return $this->total_value_delivered / $this->projects_completed;
    }

    public function isAvailable(): bool
    {
        return !$this->is_busy;
    }

    public function isBusy(): bool
    {
        return $this->is_busy;
    }

    public function assignToProject(Project $project): bool
    {
        if ($this->is_busy) {
            return false;
        }

        $this->update(['is_busy' => true]);
        
        return true;
    }

    public function releaseFromProject(): bool
    {
        return $this->update(['is_busy' => false]);
    }

    public function completeProject(Project $project): void
    {
        $this->increment('projects_completed');
        $this->increment('total_value_delivered', $project->value);
        $this->update(['is_busy' => false]);
    }

    public function getSeniorityName(): string
    {
        return match($this->seniority) {
            self::SENIORITY_JUNIOR => 'Junior',
            self::SENIORITY_JUNIOR_MID => 'Junior-Mid',
            self::SENIORITY_MID => 'Mid',
            self::SENIORITY_SENIOR => 'Senior',
            self::SENIORITY_LEAD => 'Lead',
            default => 'Unknown',
        };
    }

    public function getSeniorityStars(): string
    {
        return str_repeat('⭐', $this->seniority);
    }

    public function getSeniorityColor(): string
    {
        return match($this->seniority) {
            self::SENIORITY_JUNIOR => '#gray-500',
            self::SENIORITY_JUNIOR_MID => '#blue-500',
            self::SENIORITY_MID => '#green-500',
            self::SENIORITY_SENIOR => '#yellow-500',
            self::SENIORITY_LEAD => '#purple-500',
            default => '#gray-400',
        };
    }

    public function getSpecializationName(): ?string
    {
        return match($this->specialization) {
            self::SPECIALIZATION_FRONTEND => 'Frontend',
            self::SPECIALIZATION_BACKEND => 'Backend',
            self::SPECIALIZATION_FULLSTACK => 'Full Stack',
            self::SPECIALIZATION_MOBILE => 'Mobile',
            self::SPECIALIZATION_DEVOPS => 'DevOps',
            default => null,
        };
    }

    public function canHandleComplexity(int $complexity): bool
    {
        if ($this->seniority === self::SENIORITY_JUNIOR && $complexity > 3) {
            return false;
        }
        
        return true;
    }

    public function getSuccessProbability(int $complexity): float
    {
        $seniorityFactor = $this->seniority / 5; // 0.2 a 1.0
        $complexityPenalty = ($complexity - 1) * 0.1; // 0 a 0.4
        
        $probability = $seniorityFactor - $complexityPenalty + 0.5;
        
        return max(0.1, min(1.0, $probability));
    }

    public static function createFromMarket(Game $game, array $attributes = []): self
    {
        $seniority = $attributes['seniority'] ?? rand(1, 5);
        $specializations = [
            self::SPECIALIZATION_FRONTEND,
            self::SPECIALIZATION_BACKEND,
            self::SPECIALIZATION_FULLSTACK,
            self::SPECIALIZATION_MOBILE,
            self::SPECIALIZATION_DEVOPS,
        ];
        
        $defaults = [
            'game_id' => $game->id,
            'name' => fake()->name(),
            'seniority' => $seniority,
            'specialization' => fake()->randomElement($specializations),
            'is_busy' => false,
        ];
        
        return static::create(array_merge($defaults, $attributes));
    }

    public static function generateMarketDevelopers(int $count = 5): array
    {
        $developers = [];
        
        for ($i = 0; $i < $count; $i++) {
            $seniority = fake()->randomElement([1, 1, 2, 2, 3, 3, 4, 5]);
            
            $developers[] = [
                'name' => fake()->name(),
                'seniority' => $seniority,
                'specialization' => fake()->randomElement([
                    self::SPECIALIZATION_FRONTEND,
                    self::SPECIALIZATION_BACKEND,
                    self::SPECIALIZATION_FULLSTACK,
                    self::SPECIALIZATION_MOBILE,
                    self::SPECIALIZATION_DEVOPS,
                ]),
                'monthly_salary' => static::calculateMarketSalary($seniority),
                'is_busy' => false,
            ];
        }
        
        return $developers;
    }

    public static function calculateMarketSalary(int $seniority): float
    {
        $range = self::SALARY_RANGES[$seniority] ?? [2000, 2500];
        return (float) rand($range[0], $range[1]);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        
        $array['seniority_name'] = $this->getSeniorityName();
        $array['seniority_stars'] = $this->getSeniorityStars();
        $array['specialization_name'] = $this->getSpecializationName();
        $array['efficiency_rating'] = $this->getEfficiencyRating();
        $array['average_project_value'] = $this->getAverageProjectValue();
        $array['is_available'] = $this->isAvailable();
        
        return $array;
    }
}