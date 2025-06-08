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
 *     @OA\Property(property="generated_by_sales_person_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="name", type="string", example="E-commerce per Startup"),
 *     @OA\Property(property="complexity", type="integer", minimum=1, maximum=5, example=3),
 *     @OA\Property(property="value", type="number", format="float", example=2500.00),
 *     @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}, example="pending"),
 *     @OA\Property(property="started_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="estimated_completion_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true)
 * )
 */
class Project extends Model
{
    use HasFactory;

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    // Complexity constants
    public const COMPLEXITY_TRIVIAL = 1;
    public const COMPLEXITY_EASY = 2;
    public const COMPLEXITY_MEDIUM = 3;
    public const COMPLEXITY_HARD = 4;
    public const COMPLEXITY_EXPERT = 5;

    // Value ranges per complexity
    public const VALUE_RANGES = [
        self::COMPLEXITY_TRIVIAL => [800, 1200],
        self::COMPLEXITY_EASY => [1200, 2000],
        self::COMPLEXITY_MEDIUM => [2000, 3500],
        self::COMPLEXITY_HARD => [3500, 5500],
        self::COMPLEXITY_EXPERT => [5500, 8000],
    ];

    /**
     * Colonne mass-assignable
     * CORRETTO: Usa generated_by_sales_person_id come da migration
     */
    protected $fillable = [
        'game_id',
        'developer_id',
        'generated_by_sales_person_id',  // ✅ CORRETTO: Allineato alla migration
        'name',
        'description',
        'complexity',
        'value',
        'status',
        'assigned_at',
        'started_at',
        'estimated_completion_at',
        'completed_at',
        'estimated_duration_minutes',
        'actual_duration_minutes',
        'difficulty_multiplier',
        'rush_bonus',
        'metadata',
    ];

    /**
     * Casting automatico dei tipi
     */
    protected $casts = [
        'complexity' => 'integer',
        'value' => 'decimal:2',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'estimated_completion_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_duration_minutes' => 'integer',
        'actual_duration_minutes' => 'integer',
        'difficulty_multiplier' => 'decimal:2',
        'rush_bonus' => 'decimal:2',
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

        static::creating(function (Project $project) {
            $project->status = $project->status ?? self::STATUS_PENDING;
            $project->difficulty_multiplier = $project->difficulty_multiplier ?? 1.0;
            $project->rush_bonus = $project->rush_bonus ?? 0;
            
            if (!$project->value && $project->complexity) {
                $project->value = $project->calculateValueFromComplexity();
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

    /**
     * Calcola valore finale applicando moltiplicatori
     */
    public function getFinalValue(): float
    {
        $baseValue = $this->value;
        $difficultyAdjustment = $baseValue * ($this->difficulty_multiplier - 1);
        $rushBonus = $baseValue * $this->rush_bonus;
        
        return $baseValue + $difficultyAdjustment + $rushBonus;
    }

    /**
     * Verifica se il progetto è ad alta priorità
     */
    public function isHighPriority(): bool
    {
        return $this->rush_bonus > 0 || 
               ($this->metadata['urgency_level'] ?? '') === 'high' ||
               $this->difficulty_multiplier > 1.3;
    }

    /**
     * Verifica se il progetto è overdue
     */
    public function isOverdue(): bool
    {
        return $this->isInProgress() && 
               $this->estimated_completion_at && 
               Carbon::now()->gt($this->estimated_completion_at);
    }

    /**
     * Calcola penalty per ritardo
     */
    public function getOverduePenalty(): float
    {
        if (!$this->isOverdue()) {
            return 0.0;
        }

        $minutesOverdue = Carbon::now()->diffInMinutes($this->estimated_completion_at);
        $penaltyRate = 0.01; // 1% per ogni minuto di ritardo
        
        return min(0.5, $minutesOverdue * $penaltyRate); // Max 50% penalty
    }

    /*
    |--------------------------------------------------------------------------
    | FACTORY METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Crea progetto random per un gioco
     */
    public static function createRandomForGame(Game $game, ?SalesPerson $salesPerson = null): self
    {
        $complexity = rand(1, 5);
        $types = ['Website', 'App Mobile', 'E-commerce', 'Dashboard', 'API', 'Sistema'];
        $clients = ['Startup', 'PMI', 'Enterprise', 'Retail', 'Healthcare', 'Fintech'];
        
        return static::create([
            'game_id' => $game->id,
            'generated_by_sales_person_id' => $salesPerson?->id,
            'name' => fake()->randomElement($types) . ' per ' . fake()->randomElement($clients),
            'complexity' => $complexity,
            'status' => self::STATUS_PENDING,
        ]);
    }

    /**
     * Trova progetti pronti per il completamento
     */
    public static function findReadyForCompletion(int $toleranceSeconds = 30)
    {
        return static::inProgress()
            ->where('estimated_completion_at', '<=', Carbon::now()->addSeconds($toleranceSeconds))
            ->with(['developer', 'game', 'generatedBySalesPerson'])
            ->get();
    }

    /**
     * Trova progetti overdue
     */
    public static function findOverdue()
    {
        return static::inProgress()
            ->where('estimated_completion_at', '<', Carbon::now())
            ->with(['developer', 'game'])
            ->get();
    }

    /**
     * Statistiche progetti per gioco
     */
    public static function getStatsForGame(Game $game): array
    {
        $stats = static::where('game_id', $game->id)->selectRaw('
            status,
            COUNT(*) as count,
            AVG(value) as avg_value,
            SUM(value) as total_value,
            AVG(complexity) as avg_complexity
        ')->groupBy('status')->get();

        return $stats->keyBy('status')->toArray();
    }

    /**
     * Serialization
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        
        // Aggiungi campi calcolati
        $array['complexity_name'] = $this->getComplexityName();
        $array['complexity_stars'] = $this->getComplexityStars();
        $array['complexity_color'] = $this->getComplexityColor();
        $array['status_name'] = $this->getStatusName();
        $array['status_color'] = $this->getStatusColor();
        $array['is_assigned'] = $this->isAssigned();
        $array['current_progress'] = round($this->calculateCurrentProgress(), 1);
        $array['seconds_remaining'] = $this->getSecondsRemaining();
        $array['time_remaining_formatted'] = $this->getTimeRemainingFormatted();
        $array['hourly_value'] = round($this->getHourlyValue(), 2);
        $array['final_value'] = round($this->getFinalValue(), 2);
        $array['is_ready_for_completion'] = $this->isReadyForCompletion();
        $array['is_high_priority'] = $this->isHighPriority();
        $array['is_overdue'] = $this->isOverdue();
        $array['can_be_assigned'] = $this->canBeAssigned();
        $array['can_be_started'] = $this->canBeStarted();
        
        if ($this->isOverdue()) {
            $array['overdue_penalty'] = round($this->getOverduePenalty(), 3);
        }
        
        if ($this->completed_at && $this->started_at) {
            $array['actual_duration_minutes'] = $this->getActualDuration();
            $array['efficiency_rating'] = round($this->getEfficiencyRating() ?? 0, 1);
        }
        
        return $array;
    }
  
    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    /**
     * Sales person che ha generato il progetto
     * CORRETTO: Usa generated_by_sales_person_id
     */
    public function generatedBySalesPerson(): BelongsTo
    {
        return $this->belongsTo(SalesPerson::class, 'generated_by_sales_person_id');
    }

    /**
     * Query Scopes
     */
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

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
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

    public function scopeByValue($query, float $minValue, float $maxValue = null)
    {
        $query->where('value', '>=', $minValue);
        if ($maxValue) {
            $query->where('value', '<=', $maxValue);
        }
        return $query;
    }

    /**
     * Status Methods
     */
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

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isAssigned(): bool
    {
        return !is_null($this->developer_id);
    }

    public function canBeAssigned(): bool
    {
        return $this->isPending() && !$this->isAssigned();
    }

    public function canBeStarted(): bool
    {
        return $this->isPending() && $this->isAssigned();
    }

    /**
     * Business Logic Methods
     */
    public function assignToDeveloper(Developer $developer): bool
    {
        if (!$this->canBeAssigned() || !$developer->isAvailable()) {
            return false;
        }

        $completionTime = $developer->calculateCompletionTime($this->complexity);
        
        $this->update([
            'developer_id' => $developer->id,
            'status' => self::STATUS_IN_PROGRESS,
            'assigned_at' => Carbon::now(),
            'started_at' => Carbon::now(),
            'estimated_completion_at' => Carbon::now()->addMinutes($completionTime),
            'estimated_duration_minutes' => $completionTime,
        ]);

        $developer->assignToProject($this);

        return true;
    }

    /**
     * Completa il progetto
     */
    public function complete(): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        $developer = $this->developer;
        $completedAt = Carbon::now();
        
        $result = $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => $completedAt,
            'actual_duration_minutes' => $this->started_at ? $this->started_at->diffInMinutes($completedAt) : null,
        ]);

        if ($result) {
            // Aggiorna patrimonio del gioco
            $this->game->increment('money', $this->value);
            
            // Libera e aggiorna statistiche developer
            if ($developer) {
                $developer->completeProject($this);
            }
        }

        return $result;
    }

    /**
     * Cancella il progetto
     */
    public function cancel(string $reason = null): bool
    {
        if ($this->isCompleted()) {
            return false;
        }

        $developer = $this->developer;
        
        $metadata = $this->metadata ?? [];
        if ($reason) {
            $metadata['cancellation_reason'] = $reason;
            $metadata['cancelled_at'] = Carbon::now()->toISOString();
        }
        
        $result = $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => Carbon::now(),
            'metadata' => $metadata,
        ]);

        if ($result && $developer) {
            $developer->releaseFromProject();
        }

        return $result;
    }

    /**
     * Rimuovi assegnazione developer
     */
    public function unassign(): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        $developer = $this->developer;
        
        $result = $this->update([
            'developer_id' => null,
            'status' => self::STATUS_PENDING,
            'assigned_at' => null,
            'started_at' => null,
            'estimated_completion_at' => null,
            'estimated_duration_minutes' => null,
        ]);

        if ($result && $developer) {
            $developer->releaseFromProject();
        }

        return $result;
    }

    /**
     * Calculation Methods
     */
    public function calculateCurrentProgress(): float
    {
        if (!$this->isInProgress() || !$this->started_at || !$this->estimated_completion_at) {
            return 0.0;
        }

        $now = Carbon::now();
        $totalDuration = $this->started_at->diffInSeconds($this->estimated_completion_at);
        $elapsed = $this->started_at->diffInSeconds($now);
        
        if ($totalDuration <= 0) {
            return 100.0;
        }
        
        $progress = ($elapsed / $totalDuration) * 100;
        
        return max(0, min(100, $progress));
    }

    /**
     * Calcola secondi rimanenti
     */
    public function getSecondsRemaining(): int
    {
        if (!$this->isInProgress() || !$this->estimated_completion_at) {
            return 0;
        }

        $remaining = Carbon::now()->diffInSeconds($this->estimated_completion_at, false);
        
        return max(0, $remaining);
    }

    /**
     * Verifica se è pronto per il completamento
     */
    public function isReadyForCompletion(int $toleranceSeconds = 30): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        return $this->getSecondsRemaining() <= $toleranceSeconds;
    }

    /**
     * Calcola durata effettiva
     */
    public function getActualDuration(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }

    /**
     * Calcola valore da complessità
     */
    public function calculateValueFromComplexity(): float
    {
        $range = self::VALUE_RANGES[$this->complexity] ?? [1000, 1500];
        return (float) rand($range[0], $range[1]);
    }

    /**
     * Calcola valore orario
     */
    public function getHourlyValue(): float
    {
        $estimatedMinutes = $this->estimated_duration_minutes ?? ($this->complexity * 30);

        if ($estimatedMinutes <= 0) {
            return 0.0;
        }

        return ($this->value / $estimatedMinutes) * 60;
    }

    /**
     * Calcola efficiency rating
     */
    public function getEfficiencyRating(): ?float
    {
        if (!$this->isCompleted() || !$this->estimated_duration_minutes || !$this->actual_duration_minutes) {
            return null;
        }

        return ($this->estimated_duration_minutes / $this->actual_duration_minutes) * 100;
    }

    /**
     * Display Methods
     */
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
            self::COMPLEXITY_TRIVIAL => '#10b981',   // green-500
            self::COMPLEXITY_EASY => '#3b82f6',     // blue-500
            self::COMPLEXITY_MEDIUM => '#f59e0b',   // yellow-500
            self::COMPLEXITY_HARD => '#f97316',     // orange-500
            self::COMPLEXITY_EXPERT => '#ef4444',   // red-500
            default => '#6b7280',                   // gray-500
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => '#6b7280',      // gray-500
            self::STATUS_IN_PROGRESS => '#3b82f6',  // blue-500
            self::STATUS_COMPLETED => '#10b981',    // green-500
            self::STATUS_CANCELLED => '#ef4444',    // red-500
            default => '#6b7280',                   // gray-500
        };
    }

    public function getStatusName(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'In Attesa',
            self::STATUS_IN_PROGRESS => 'In Corso',
            self::STATUS_COMPLETED => 'Completato',
            self::STATUS_CANCELLED => 'Cancellato',
            default => 'Sconosciuto',
        };
    }

}