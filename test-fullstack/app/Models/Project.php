<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="Project",
 *     title="Project Model",
 *     description="Rappresenta un progetto nel gioco",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="game_id", type="integer", example=1),
 *     @OA\Property(property="developer_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="created_by_sales_person_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="name", type="string", example="E-commerce per Startup"),
 *     @OA\Property(property="complexity", type="integer", minimum=1, maximum=5, example=3),
 *     @OA\Property(property="value", type="number", format="float", example=2500.00),
 *     @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "failed"}, example="pending"),
 *     @OA\Property(property="started_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="estimated_completion_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="notes", type="string", nullable=true)
 * )
 */
class Project extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const COMPLEXITY_TRIVIAL = 1;
    public const COMPLEXITY_EASY = 2;
    public const COMPLEXITY_MEDIUM = 3;
    public const COMPLEXITY_HARD = 4;
    public const COMPLEXITY_EXPERT = 5;

    public const VALUE_RANGES = [
        self::COMPLEXITY_TRIVIAL => [800, 1200],
        self::COMPLEXITY_EASY => [1200, 2000],
        self::COMPLEXITY_MEDIUM => [2000, 3500],
        self::COMPLEXITY_HARD => [3500, 5500],
        self::COMPLEXITY_EXPERT => [5500, 8000],
    ];

    protected $fillable = [
        'game_id',
        'developer_id',
        'created_by_sales_person_id',
        'name',
        'complexity',
        'value',
        'status',
        'started_at',
        'estimated_completion_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'complexity' => 'integer',
        'value' => 'decimal:2',
        'started_at' => 'datetime',
        'estimated_completion_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Project $project) {
            $project->status = $project->status ?? self::STATUS_PENDING;
            
            if (!$project->value && $project->complexity) {
                $project->value = $project->calculateValueFromComplexity();
            }
        });
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    public function createdBySalesPerson(): BelongsTo
    {
        return $this->belongsTo(SalesPerson::class, 'created_by_sales_person_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeByComplexity($query, int $complexity)
    {
        return $query->where('complexity', $complexity);
    }

    public function scopeAssigned($query)
    {
        return $query->whereNotNull('developer_id');
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('developer_id');
    }


    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    } 

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isAssigned(): bool
    {
        return !is_null($this->developer_id);
    }

    public function assignToDeveloper(Developer $developer): bool
    {
        if (!$this->isPending() || !$developer->isAvailable()) {
            return false;
        }

        $completionTime = $developer->calculateCompletionTime($this->complexity);
        
        $this->update([
            'developer_id' => $developer->id,
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => Carbon::now(),
            'estimated_completion_at' => Carbon::now()->addMinutes($completionTime),
        ]);

        $developer->assignToProject($this);

        return true;
    }

    public function complete(): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        $developer = $this->developer;
        
        $result = $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => Carbon::now(),
        ]);

        if ($result && $developer) {
            $this->game->increment('money', $this->value);
            
            $developer->completeProject($this);
        }

        return $result;
    }

    public function fail(string $reason = null): bool
    {
        if ($this->isCompleted()) {
            return false;
        }

        $developer = $this->developer;
        
        $result = $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => Carbon::now(),
            'notes' => $this->notes ? $this->notes . "\nFailed: {$reason}" : "Failed: {$reason}",
        ]);

        if ($result && $developer) {
            $developer->releaseFromProject();
        }

        return $result;
    }

    public function unassign(): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        $developer = $this->developer;
        
        $result = $this->update([
            'developer_id' => null,
            'status' => self::STATUS_PENDING,
            'started_at' => null,
            'estimated_completion_at' => null,
        ]);

        if ($result && $developer) {
            $developer->releaseFromProject();
        }

        return $result;
    }

    public function calculateCurrentProgress(): float
    {
        if (!$this->isInProgress() || !$this->started_at || !$this->estimated_completion_at) {
            return 0.0;
        }

        $now = Carbon::now();
        $totalDuration = $this->started_at->diffInSeconds($this->estimated_completion_at);
        $elapsed = $this->started_at->diffInSeconds($now);
        
        $progress = ($elapsed / $totalDuration) * 100;
        
        return max(0, min(100, $progress));
    }

    public function getSecondsRemaining(): int
    {
        if (!$this->isInProgress() || !$this->estimated_completion_at) {
            return 0;
        }

        $remaining = Carbon::now()->diffInSeconds($this->estimated_completion_at, false);
        
        return max(0, $remaining);
    }

    public function isReadyForCompletion(int $toleranceSeconds = 30): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        return $this->getSecondsRemaining() <= $toleranceSeconds;
    }

    public function getActualDuration(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }

    public function calculateValueFromComplexity(): float
    {
        $range = self::VALUE_RANGES[$this->complexity] ?? [1000, 1500];
        return (float) rand($range[0], $range[1]);
    }

    public function getHourlyValue(): float
    {
        $estimatedMinutes = $this->started_at && $this->estimated_completion_at 
            ? $this->started_at->diffInMinutes($this->estimated_completion_at)
            : $this->complexity * 30;

        if ($estimatedMinutes <= 0) {
            return 0.0;
        }

        return ($this->value / $estimatedMinutes) * 60;
    }

    public function getComplexityName(): string
    {
        return match($this->complexity) {
            self::COMPLEXITY_TRIVIAL => 'Triviale',
            self::COMPLEXITY_EASY => 'Facile',
            self::COMPLEXITY_MEDIUM => 'Medio',
            self::COMPLEXITY_HARD => 'Difficile',
            self::COMPLEXITY_EXPERT => 'Esperto',
            default => 'Sconosciuto',
        };
    }

    public function getComplexityStars(): string
    {
        return str_repeat('⭐', $this->complexity);
    }

    public function getComplexityColor(): string
    {
        return match($this->complexity) {
            self::COMPLEXITY_TRIVIAL => '#green-500',
            self::COMPLEXITY_EASY => '#blue-500',
            self::COMPLEXITY_MEDIUM => '#yellow-500',
            self::COMPLEXITY_HARD => '#orange-500',
            self::COMPLEXITY_EXPERT => '#red-500',
            default => '#gray-400',
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => '#gray-500',
            self::STATUS_IN_PROGRESS => '#blue-500',
            self::STATUS_COMPLETED => '#green-500',
            self::STATUS_FAILED => '#red-500',
            default => '#gray-400',
        };
    }

    public function getStatusName(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'In Attesa',
            self::STATUS_IN_PROGRESS => 'In Corso',
            self::STATUS_COMPLETED => 'Completato',
            self::STATUS_FAILED => 'Fallito',
            default => 'Sconosciuto',
        };
    }

    public function getTimeRemainingFormatted(): string
    {
        $seconds = $this->getSecondsRemaining();
        
        if ($seconds <= 0) {
            return 'Completato';
        }

        $minutes = intval($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$remainingSeconds}s";
        }

        return "{$remainingSeconds}s";
    }

    public static function createRandomForGame(Game $game, ?SalesPerson $salesPerson = null): self
    {
        $complexity = rand(1, 5);
        $types = ['Website', 'App Mobile', 'E-commerce', 'Dashboard', 'API', 'Sistema'];
        $clients = ['Startup', 'PMI', 'Enterprise', 'Retail', 'Healthcare', 'Fintech'];
        
        return static::create([
            'game_id' => $game->id,
            'created_by_sales_person_id' => $salesPerson?->id,
            'name' => fake()->randomElement($types) . ' per ' . fake()->randomElement($clients),
            'complexity' => $complexity,
            'status' => self::STATUS_PENDING,
        ]);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
                $array['complexity_name'] = $this->getComplexityName();
        $array['complexity_stars'] = $this->getComplexityStars();
        $array['status_name'] = $this->getStatusName();
        $array['is_assigned'] = $this->isAssigned();
        $array['current_progress'] = $this->calculateCurrentProgress();
        $array['seconds_remaining'] = $this->getSecondsRemaining();
        $array['time_remaining_formatted'] = $this->getTimeRemainingFormatted();
        $array['hourly_value'] = $this->getHourlyValue();
        $array['is_ready_for_completion'] = $this->isReadyForCompletion();
        
        if ($this->completed_at && $this->started_at) {
            $array['actual_duration_minutes'] = $this->getActualDuration();
        }
        
        return $array;
    }
}