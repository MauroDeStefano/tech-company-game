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
 *     @OA\Property(property="estimated_value", type="number", format="float", example=2500.00),
 *     @OA\Property(property="started_at", type="string", format="date-time"),
 *     @OA\Property(property="estimated_completion_at", type="string", format="date-time"),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true)
 * )
 */
class ProjectGeneration extends Model
{
    use HasFactory;

    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'game_id',
        'sales_person_id',
        'actual_project_id',
        'status',
        'estimated_value',
        'started_at',
        'estimated_completion_at',
        'completed_at',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'started_at' => 'datetime',
        'estimated_completion_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProjectGeneration $generation) {
            $generation->status = $generation->status ?? self::STATUS_IN_PROGRESS;
            $generation->started_at = $generation->started_at ?? Carbon::now();
        });
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(SalesPerson::class);
    }

    public function actualProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'actual_project_id');
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

    public function scopeBySalesPerson($query, int $salesPersonId)
    {
        return $query->where('sales_person_id', $salesPersonId);
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

    public function complete(): ?Project
    {
        if (!$this->isInProgress()) {
            return null;
        }

        $salesPerson = $this->salesPerson;
        if (!$salesPerson) {
            return null;
        }

        $project = $salesPerson->completeProjectGeneration($this);

        return $project;
    }

    public function cancel(string $reason = null): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        $result = $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => Carbon::now(),
        ]);

        if ($result) {
            $this->salesPerson?->releaseFromGeneration();
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
        
        if ($totalDuration <= 0) {
            return 100.0;
        }
        
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

    public function getEstimatedDuration(): int
    {
        if (!$this->started_at || !$this->estimated_completion_at) {
            return 0;
        }

        return $this->started_at->diffInMinutes($this->estimated_completion_at);
    }

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
            self::STATUS_IN_PROGRESS => '#blue-500',
            self::STATUS_COMPLETED => '#green-500',
            self::STATUS_CANCELLED => '#red-500',
            default => '#gray-400',
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
        $value = number_format($this->estimated_value, 2);
        
        return "Progetto da {$value}€ generato da {$salesPersonName}";
    }

    public function canBeCancelled(): bool
    {
        return $this->isInProgress();
    }

    public function canBeCompleted(): bool
    {
        return $this->isInProgress() && $this->isReadyForCompletion();
    }

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

    public function wasCompletedEarly(): ?bool
    {
        if (!$this->isCompleted()) {
            return null;
        }

        $efficiency = $this->getEfficiencyRating();
        return $efficiency !== null && $efficiency > 100;
    }

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

    public static function startForSalesPerson(SalesPerson $salesPerson): ?self
    {
        if ($salesPerson->isBusy()) {
            return null;
        }

        $generation = static::create([
            'game_id' => $salesPerson->game_id,
            'sales_person_id' => $salesPerson->id,
            'estimated_value' => $salesPerson->calculateProjectValue(),
            'estimated_completion_at' => Carbon::now()->addMinutes($salesPerson->calculateGenerationTime()),
        ]);

        $salesPerson->update(['is_busy' => true]);

        return $generation;
    }

    public static function findReadyForCompletion(int $toleranceSeconds = 30)
    {
        return static::inProgress()
            ->where('estimated_completion_at', '<=', Carbon::now()->addSeconds($toleranceSeconds))
            ->with(['salesPerson', 'game'])
            ->get();
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['status_name'] = $this->getStatusName();
        $array['current_progress'] = $this->calculateCurrentProgress();
        $array['seconds_remaining'] = $this->getSecondsRemaining();
        $array['time_remaining_formatted'] = $this->getTimeRemainingFormatted();
        $array['description'] = $this->getDescription();
        $array['estimated_duration_minutes'] = $this->getEstimatedDuration();
        $array['is_ready_for_completion'] = $this->isReadyForCompletion();
        $array['can_be_cancelled'] = $this->canBeCancelled();
        
        if ($this->isCompleted()) {
            $array['actual_duration_minutes'] = $this->getActualDuration();
            $array['efficiency_rating'] = $this->getEfficiencyRating();
            $array['was_completed_early'] = $this->wasCompletedEarly();
            $array['time_difference_minutes'] = $this->getTimeDifferenceMinutes();
        }
        
        return $array;
    }
}