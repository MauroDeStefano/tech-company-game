<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class Game extends Model
{
    // Costanti status
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_GAME_OVER = 'game_over';
    const INITIAL_MONEY = 5000;

    protected $fillable = [
        'name',
        'money',
        'status',
        'notes',
        'paused_at',
        'resumed_at',
        'offline_duration_seconds',
        'initial_setup_completed',
        'user_id'
    ];

    protected $casts = [
        'money' => 'decimal:2',
        'paused_at' => 'datetime',
        'resumed_at' => 'datetime',
        'offline_duration_seconds' => 'integer',
        'initial_setup_completed' => 'boolean',
    ];

    // 🎯 Relations
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

    // 🎯 Status check methods
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

    // 🎯 Pause/Resume methods
    public function pause(): bool
    {
        return $this->update([
            'status' => self::STATUS_PAUSED,
            'paused_at' => Carbon::now(),
        ]);
    }

    public function resume(): bool
    {
        $wasResumed = $this->update([
            'status' => self::STATUS_ACTIVE,
            'resumed_at' => Carbon::now(),
        ]);

        if ($this->paused_at && $wasResumed) {
            $offlineSeconds = Carbon::now()->diffInSeconds($this->paused_at);
            $this->increment('offline_duration_seconds', $offlineSeconds);
            $this->update(['paused_at' => null]);
        }

        return $wasResumed;
    }

    // 🎯 AGGIUNTA: Metodo per calcolare costi mensili
    public function calculateMonthlyCosts(): float
    {
        try {
            // Costi sviluppatori (se esistono)
            $developersCosts = $this->developers()->sum('salary') ?: 0;
            
            // Costi commerciali (se esistono)
            $salesCosts = $this->salesPeople()->sum('salary') ?: 0;
            
            // Costi fissi azienda (affitto, utenze, ecc.)
            $fixedCosts = 1500;
            
            return $developersCosts + $salesCosts + $fixedCosts;
        } catch (\Exception $e) {
            // Fallback: solo costi fissi se c'è un errore
            return 1500;
        }
    }

    // 🎯 AGGIUNTA: Metodo per calcolare ore di gioco
    public function calculatePlayTimeHours(): float
    {
        try {
            $totalSeconds = $this->updated_at->diffInSeconds($this->created_at);
            $playSeconds = $totalSeconds - ($this->offline_duration_seconds ?? 0);
            return round($playSeconds / 3600, 1);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    // 🎯 AGGIUNTA: Route Model Binding personalizzato
    public function getRouteKeyName()
    {
        return 'id';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $game = $this->where($field ?? $this->getRouteKeyName(), $value)->first();
        
        if (!$game) {
            throw new ModelNotFoundException("Game with ID {$value} not found.");
        }
        
        return $game;
    }

    // 🎯 AGGIUNTA: Scope per filtrare per utente
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // 🎯 AGGIUNTA: Verifica ownership
    public function isOwnedBy($user): bool
    {
        return $this->user_id === ($user->id ?? $user);
    }

    // 🎯 AGGIUNTA: Check condizioni game over
    public function checkGameOverConditions(): bool
    {
        try {
            // Game over se patrimonio negativo e nessun progetto attivo
            if ($this->money < 0) {
                $activeProjects = $this->projects()->where('status', 'in_progress')->count();
                $pendingProjects = $this->projects()->where('status', 'pending')->count();
                
                // Se non ci sono progetti che possono generare entrate
                if ($activeProjects === 0 && $pendingProjects === 0) {
                    $this->update(['status' => self::STATUS_GAME_OVER]);
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    // 🎯 AGGIUNTA: Accessor per nome formattato
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: 'Partita del ' . $this->created_at->format('d/m/Y H:i');
    }

    // 🎯 AGGIUNTA: Helper per revenue totale
    public function getTotalRevenueAttribute(): float
    {
        try {
            return $this->projects()
                ->where('status', 'completed')
                ->sum('value');
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    // 🎯 AGGIUNTA: Helper per profit margin
    public function getProfitMarginAttribute(): float
    {
        try {
            $revenue = $this->total_revenue;
            if ($revenue <= 0) return 0;
            
            $costs = $this->calculateMonthlyCosts() * max(1, $this->created_at->diffInMonths(Carbon::now()));
            
            return round((($revenue - $costs) / $revenue) * 100, 2);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    // 🎯 AGGIUNTA: Rating efficienza per UI
    public function getEfficiencyRatingAttribute(): string
    {
        $margin = $this->profit_margin;
        
        if ($margin >= 50) return 'Eccellente';
        if ($margin >= 30) return 'Buono';
        if ($margin >= 10) return 'Sufficiente';
        if ($margin >= 0) return 'Scarso';
        
        return 'In Perdita';
    }
}