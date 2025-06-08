<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="ProjectGeneration",
 *     title="Project Generation Model",
 *     description="Rappresenta una generazione di progetto in corso da parte di un commerciale",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="game_id", type="integer", example=1),
 *     @OA\Property(property="sales_person_id", type="integer", example=1),
 *     @OA\Property(property="actual_project_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="status", type="string", enum={"in_progress", "completed", "cancelled"}, example="in_progress"),
 *     @OA\Property(property="target_value", type="number", format="float", example=2500.00),
 *     @OA\Property(property="started_at", type="string", format="date-time"),
 *     @OA\Property(property="estimated_completion_at", type="string", format="date-time"),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true)
 * )
 */
class ProjectGeneration extends Model
{
    use HasFactory;

    // Status constants
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Colonne mass-assignable
     * CORRETTO: Usa actual_project_id come da migration corretta
     */
    protected $fillable = [
        'game_id',
        'sales_person_id',
        'actual_project_id',                // ✅ CORRETTO: Allineato alla migration
        'status',
        'started_at',
        'estimated_completion_at',
        'completed_at',
        'estimated_duration_minutes',
        'actual_duration_minutes',
        'target_complexity',
        'target_value',
        'target_name',
        'experience_multiplier',
        'market_conditions',
        'generation_parameters',
    ];

    /**
     * Casting automatico dei tipi
     */
    protected $casts = [
        'started_at' => 'datetime',
        'estimated_completion_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_duration_minutes' => 'integer',
        'actual_duration_minutes' => 'integer',
        'target_complexity' => 'integer',
        'target_value' => 'decimal:2',
        'experience_multiplier' => 'decimal:2',
        'market_conditions' => 'decimal:2',
        'generation_parameters' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Valori di default per nuovi record
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProjectGeneration $generation) {
            $generation->status = $generation->status ?? self::STATUS_IN_PROGRESS;
            $generation->started_at = $generation->started_at ?? Carbon::now();
            $generation->experience_multiplier = $generation->experience_multiplier ?? 1.0;
            $generation->market_conditions = $generation->market_conditions ?? 1.0;
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

    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(SalesPerson::class);
    }

    /**
     * Progetto effettivamente generato
     * CORRETTO: Usa actual_project_id
     */
    public function actualProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'actual_project_id');
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

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

    public function scopeBySalesPerson($query, int $salesPersonId)
    {
        return $query->where('sales_person_id', $salesPersonId);
    }

    public function scopeByGame($query, int $gameId)
    {
        return $query->where('game_id', $gameId);
    }

    public function scopeWithTargetValue($query, float $minValue, float $maxValue = null)
    {
        $query->where('target_value', '>=', $minValue);
        if ($maxValue) {
            $query->where('target_value', '<=', $maxValue);
        }
        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS METHODS
    |--------------------------------------------------------------------------
    */

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

    public function canBeCompleted(): bool
    {
        return $this->isInProgress() && $this->isReadyForCompletion();
    }

    public function canBeCancelled(): bool
    {
        return $this->isInProgress();
    }

    /*
    |--------------------------------------------------------------------------
    | BUSINESS LOGIC METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Completa la generazione creando il progetto
     */
    public function complete(): ?Project
    {
        if (!$this->isInProgress()) {
            return null;
        }

        $salesPerson = $this->salesPerson;
        if (!$salesPerson) {
            return null;
        }

        // Crea il progetto basato sui parametri target
        $project = Project::create([
            'game_id' => $this->game_id,
            'generated_by_sales_person_id' => $this->sales_person_id,
            'name' => $this->target_name ?? 'Progetto Generato',
            'complexity' => $this->target_complexity ?? 3,
            'value' => $this->target_value ?? 2000,
            'status' => Project::STATUS_PENDING,
        ]);

        // Aggiorna la generazione
        $completedAt = Carbon::now();
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => $completedAt,
            'actual_project_id' => $project->id,
            'actual_duration_minutes' => $this->started_at ? $this->started_at->diffInMinutes($completedAt) : null,
        ]);

        // Aggiorna statistiche del sales person
        $salesPerson->completeGeneration($project);

        return $project;
    }

    /**
     * Cancella la generazione
     */
    public function cancel(string $reason = null): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        $parameters = $this->generation_parameters ?? [];
        if ($reason) {
            $parameters['cancellation_reason'] = $reason;
            $parameters['cancelled_at'] = Carbon::now()->toISOString();
        }

        $result = $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => Carbon::now(),
            'generation_parameters' => $parameters,
        ]);

        if ($result) {
            $this->salesPerson?->finishGeneration();
        }

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATION METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Calcola progresso corrente
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
     * Calcola durata stimata
     */
    public function getEstimatedDuration(): int
    {
        if (!$this->started_at || !$this->estimated_completion_at) {
            return $this->estimated_duration_minutes ?? 60;
        }

        return $this->started_at->diffInMinutes($this->estimated_completion_at);
    }

    /**
     * Calcola efficiency rating
     */
    public function getEfficiencyRating(): ?float
    {
        if (!$this->isCompleted()) {
            return null;
        }

        $estimatedMinutes = $this->getEstimatedDuration();
        $actualMinutes = $this->getActualDuration();

        if ($estimatedMinutes <= 0 || $actualMinutes <= 0) {
            return null;
        }

        return ($estimatedMinutes / $actualMinutes) * 100;
    }

    /**
     * Verifica se completato in anticipo
     */
    public function wasCompletedEarly(): ?bool
    {
        if (!$this->isCompleted()) {
            return null;
        }

        $efficiency = $this->getEfficiencyRating();
        return $efficiency !== null && $efficiency > 100;
    }

    /**
     * Differenza di tempo in minuti
     */
    public function getTimeDifferenceMinutes(): ?int
    {
        if (!$this->isCompleted()) {
            return null;
        }

        $estimatedMinutes = $this->getEstimatedDuration();
        $actualMinutes = $this->getActualDuration();

        if ($estimatedMinutes <= 0 || $actualMinutes <= 0) {
            return null;
        }

        return $actualMinutes - $estimatedMinutes;
    }

    /*
    |--------------------------------------------------------------------------
    | DISPLAY METHODS
    |--------------------------------------------------------------------------
    */

    public function getStatusName(): string
    {
        return match($this->status) {
            self::STATUS_IN_PROGRESS => 'In Corso',
            self::STATUS_COMPLETED => 'Completato',
            self::STATUS_CANCELLED => 'Cancellato',
            default => 'Sconosciuto',
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_IN_PROGRESS => '#3b82f6',  // blue-500
            self::STATUS_COMPLETED => '#10b981',    // green-500
            self::STATUS_CANCELLED => '#ef4444',    // red-500
            default => '#6b7280',                   // gray-500
        };
    }

    public function getTimeRemainingFormatted(): string
    {
        $seconds = $this->getSecondsRemaining();
        
        if ($seconds <= 0) {
            return 'Pronto';
        }

        $minutes = intval($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$remainingSeconds}s";
        }

        return "{$remainingSeconds}s";
    }

    public function getDescription(): string
    {
        $salesPersonName = $this->salesPerson?->name ?? 'Commerciale sconosciuto';
        $value = number_format($this->target_value ?? 0, 2);
        
        return "Progetto da {$value}€ generato da {$salesPersonName}";
    }

    public function getTargetComplexityName(): string
    {
        return match($this->target_complexity) {
            1 => 'Triviale',
            2 => 'Facile', 
            3 => 'Medio',
            4 => 'Difficile',
            5 => 'Esperto',
            default => 'Non specificato',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | FACTORY METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Avvia generazione per un sales person
     */
    public static function startForSalesPerson(SalesPerson $salesPerson): ?self
    {
        if ($salesPerson->isBusy()) {
            return null;
        }

        $generationTime = $salesPerson->calculateGenerationTime();
        $targetValue = $salesPerson->calculateProjectValue();

        $generation = static::create([
            'game_id' => $salesPerson->game_id,
            'sales_person_id' => $salesPerson->id,
            'target_value' => $targetValue,
            'target_complexity' => rand(1, 5),
            'target_name' => 'Progetto ' . fake()->company(),
            'estimated_completion_at' => Carbon::now()->addMinutes($generationTime),
            'estimated_duration_minutes' => $generationTime,
            'experience_multiplier' => 0.8 + ($salesPerson->experience * 0.1),
        ]);

        $salesPerson->startGeneration();

        return $generation;
    }

    /**
     * Trova generazioni pronte per il completamento
     */
    public static function findReadyForCompletion(int $toleranceSeconds = 30)
    {
        return static::inProgress()
            ->where('estimated_completion_at', '<=', Carbon::now()->addSeconds($toleranceSeconds))
            ->with(['salesPerson', 'game'])
            ->get();
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
        $array['status_name'] = $this->getStatusName();
        $array['current_progress'] = $this->calculateCurrentProgress();
        $array['seconds_remaining'] = $this->getSecondsRemaining();
        $array['time_remaining_formatted'] = $this->getTimeRemainingFormatted();
        $array['description'] = $this->getDescription();
        $array['estimated_duration_minutes'] = $this->getEstimatedDuration();
        $array['is_ready_for_completion'] = $this->isReadyForCompletion();
        $array['can_be_cancelled'] = $this->canBeCancelled();
        $array['target_complexity_name'] = $this->getTargetComplexityName();
        
        if ($this->isCompleted()) {
            $array['actual_duration_minutes'] = $this->getActualDuration();
            $array['efficiency_rating'] = $this->getEfficiencyRating();
            $array['was_completed_early'] = $this->wasCompletedEarly();
            $array['time_difference_minutes'] = $this->getTimeDifferenceMinutes();
        }
        
        return $array;
    }
}