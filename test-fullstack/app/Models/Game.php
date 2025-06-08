<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="Game",
 *     title="Game Model",
 *     description="Rappresenta una partita del gioco gestionale",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="La mia software house", nullable=true),
 *     @OA\Property(property="money", type="number", format="float", example=5000.00),
 *     @OA\Property(property="status", type="string", enum={"active", "paused", "game_over"}, example="active"),
 *     @OA\Property(property="notes", type="string", example="Strategia vincente!", nullable=true),
 *     @OA\Property(property="initial_setup_completed", type="boolean", example=true),
 *     @OA\Property(property="paused_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="resumed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="offline_duration_seconds", type="integer", example=3600),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Game extends Model
{
    use HasFactory, SoftDeletes;

    // Costanti per gli stati del gioco
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_GAME_OVER = 'game_over';

    // Configurazione iniziale del gioco
    public const INITIAL_MONEY = 5000.00;
    public const INITIAL_DEVELOPERS = 1;
    public const INITIAL_SALES_PEOPLE = 1;
    public const MIN_MONEY_THRESHOLD = -1000.00; // Soglia game over

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'name',
        'money',
        'status',
        'notes',
        'initial_setup_completed',
        'paused_at',
        'resumed_at',
        'offline_duration_seconds',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'money' => 'decimal:2',
        'initial_setup_completed' => 'boolean',
        'paused_at' => 'datetime',
        'resumed_at' => 'datetime',
        'offline_duration_seconds' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Game $game) {
            $game->money = $game->money ?? self::INITIAL_MONEY;
            $game->status = $game->status ?? self::STATUS_ACTIVE;
            $game->offline_duration_seconds = $game->offline_duration_seconds ?? 0;
            $game->initial_setup_completed = false;
            
            if (!$game->user_id && auth()->check()) {
                $game->user_id = auth()->id();
            }
        });

        static::created(function (Game $game) {
            if (!$game->initial_setup_completed) {
                $game->initializeWithStartingTeam();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function developers(): HasMany
    {
        return $this->hasMany(Developer::class);
    }

    public function salesPeople(): HasMany
    {
        return $this->hasMany(SalesPerson::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function projectGenerations(): HasMany
    {
        return $this->hasMany(ProjectGeneration::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePaused($query)
    {
        return $query->where('status', self::STATUS_PAUSED);
    }

    public function scopeGameOver($query)
    {
        return $query->where('status', self::STATUS_GAME_OVER);
    }

    public function scopeForCurrentUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPaused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function isGameOver(): bool
    {
        return $this->status === self::STATUS_GAME_OVER;
    }

    public function isNearBankruptcy(): bool
    {
        return $this->money <= self::MIN_MONEY_THRESHOLD;
    }

    public function pause(): bool
    {
        if ($this->isGameOver()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_PAUSED,
            'paused_at' => Carbon::now(),
        ]);
    }

    public function resume(): bool
    {
        if ($this->isGameOver()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_ACTIVE,
            'resumed_at' => Carbon::now(),
        ]);
    }

    public function endGame(string $reason = 'bankruptcy'): bool
    {
        return $this->update([
            'status' => self::STATUS_GAME_OVER,
            'notes' => $this->notes . "\nGame Over: {$reason}",
        ]);
    }

    public function checkGameOverConditions(): bool
    {
        if ($this->isNearBankruptcy()) {
            $this->endGame('bankruptcy');
            return true;
        }

        return false;
    }

    public function calculateMonthlyCosts(): float
    {
        $developerCosts = $this->developers()->sum('monthly_salary');
        $salesCosts = $this->salesPeople()->sum('monthly_salary');
        
        return (float) ($developerCosts + $salesCosts);
    }

    public function calculateTotalRevenue(): float
    {
        return (float) $this->projects()
            ->where('status', 'completed')
            ->sum('value');
    }

    public function calculateProfitMargin(): float
    {
        $revenue = $this->calculateTotalRevenue();
        $costs = $this->calculateMonthlyCosts() * $this->getActiveMonths();
        
        if ($revenue <= 0) {
            return 0.0;
        }
        
        return (($revenue - $costs) / $revenue) * 100;
    }

    public function getActiveMonths(): int
    {
        $months = $this->created_at->diffInMonths(Carbon::now());
        return max(1, $months);
    }

    public function getAvailableDevelopers()
    {
        return $this->developers()->where('is_busy', false)->get();
    }

    public function getAvailableSalesPeople()
    {
        return $this->salesPeople()->where('is_busy', false)->get();
    }

    public function getPendingProjects()
    {
        return $this->projects()->where('status', 'pending')->get();
    }

    public function getActiveProjects()
    {
        return $this->projects()->where('status', 'in_progress')->get();
    }

    public function initializeWithStartingTeam(): void
    {
        \DB::transaction(function () {
            for ($i = 0; $i < self::INITIAL_DEVELOPERS; $i++) {
                Developer::factory()->forGame($this)->create([
                    'seniority' => 2,
                    'monthly_salary' => 2500,
                    'is_busy' => false,
                ]);
            }

            for ($i = 0; $i < self::INITIAL_SALES_PEOPLE; $i++) {
                SalesPerson::factory()->forGame($this)->create([
                    'experience' => 2,
                    'monthly_salary' => 2000,
                    'is_busy' => false,
                ]);
            }

            $this->generateInitialProjects();

            $this->update(['initial_setup_completed' => true]);
        });
    }

    private function generateInitialProjects(): void
    {
        for ($i = 0; $i < rand(2, 3); $i++) {
            Project::factory()->forGame($this)->create([
                'complexity' => rand(1, 3),
                'value' => rand(1000, 3000),
                'status' => 'pending',
            ]);
        }
    }

    public function getDisplayName(): string
    {
        return $this->name ?? "Partita del {$this->created_at->format('d/m/Y H:i')}";
    }

    public function getGameStats(): array
    {
        return [
            'total_revenue' => $this->calculateTotalRevenue(),
            'monthly_costs' => $this->calculateMonthlyCosts(),
            'profit_margin' => $this->calculateProfitMargin(),
            'team_size' => $this->developers()->count() + $this->salesPeople()->count(),
            'completed_projects' => $this->projects()->where('status', 'completed')->count(),
            'active_projects' => $this->projects()->where('status', 'in_progress')->count(),
            'pending_projects' => $this->projects()->where('status', 'pending')->count(),
            'game_age_days' => $this->created_at->diffInDays(Carbon::now()),
        ];
    }

    public function addOfflineTime(int $seconds): bool
    {
        return $this->update([
            'offline_duration_seconds' => $this->offline_duration_seconds + $seconds,
        ]);
    }

    public function getOfflineTimeFormatted(): string
    {
        $seconds = $this->offline_duration_seconds;
        
        if ($seconds < 60) {
            return "{$seconds} secondi";
        } elseif ($seconds < 3600) {
            $minutes = intval($seconds / 60);
            return "{$minutes} minuti";
        } else {
            $hours = intval($seconds / 3600);
            $remainingMinutes = intval(($seconds % 3600) / 60);
            
            if ($remainingMinutes > 0) {
                return "{$hours} ore e {$remainingMinutes} minuti";
            }
            
            return "{$hours} ore";
        }
    }

    public function canAfford(float $cost): bool
    {
        return $this->money >= $cost;
    }

    public function canAssignProject(): bool
    {
        return $this->getAvailableDevelopers()->count() > 0 && 
               $this->getPendingProjects()->count() > 0;
    }

    public function canGenerateProject(): bool
    {
        return $this->getAvailableSalesPeople()->count() > 0;
    }

    public function loadForDashboard()
    {
        return $this->load([
            'developers' => function ($query) {
                $query->orderBy('seniority', 'desc');
            },
            'salesPeople' => function ($query) {
                $query->orderBy('experience', 'desc');
            },
            'projects' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'projectGenerations' => function ($query) {
                $query->where('status', 'in_progress')
                      ->orderBy('estimated_completion_at', 'asc');
            }
        ]);
    }

    public function scopeWithFullData($query)
    {
        return $query->with([
            'user:id,name,email',
            'developers:id,game_id,name,seniority,monthly_salary,is_busy',
            'salesPeople:id,game_id,name,experience,monthly_salary,is_busy',
            'projects:id,game_id,developer_id,name,complexity,value,status,created_at',
            'projectGenerations:id,game_id,sales_person_id,status,estimated_completion_at'
        ]);
    }

    public function save(array $options = []): bool
    {
        $result = parent::save($options);
        
        if ($result && $this->isActive()) {
            $this->checkGameOverConditions();
        }
        
        return $result;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        
        $array['display_name'] = $this->getDisplayName();
        $array['monthly_costs'] = $this->calculateMonthlyCosts();
        $array['total_revenue'] = $this->calculateTotalRevenue();
        $array['profit_margin'] = $this->calculateProfitMargin();
        $array['is_near_bankruptcy'] = $this->isNearBankruptcy();
        $array['offline_time_formatted'] = $this->getOfflineTimeFormatted();
        
        return $array;
    }

    public static function createForCurrentUser(array $attributes = []): self
    {
        $attributes['user_id'] = auth()->id();
        
        return static::create($attributes);
    }

    public static function findForCurrentUser(int $gameId): ?self
    {
        return static::where('user_id', auth()->id())
                    ->where('id', $gameId)
                    ->first();
    }

    public static function getAllForCurrentUser()
    {
        return static::where('user_id', auth()->id())
                    ->orderBy('updated_at', 'desc')
                    ->get();
    }
}