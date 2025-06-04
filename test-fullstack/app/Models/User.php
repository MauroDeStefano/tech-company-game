<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User Model",
 *     description="Rappresenta un utente del sistema",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Mario Rossi"),
 *     @OA\Property(property="email", type="string", format="email", example="mario.rossi@example.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function activeGames(): HasMany
    {
        return $this->hasMany(Game::class)->where('status', Game::STATUS_ACTIVE);
    }

    public function completedGames(): HasMany
    {
        return $this->hasMany(Game::class)->where('status', Game::STATUS_GAME_OVER);
    }

    public function createGameToken(string $name = 'game-token'): NewAccessToken
    {
        $this->tokens()->where('name', $name)->delete();
        
        return $this->createToken($name, ['game:access']);
    }

    public function hasValidToken(): bool
    {
        return $this->tokens()->where('name', 'game-token')->exists();
    }

    public function getGameStatistics(): array
    {
        $games = $this->games;
        
        return [
            'total_games' => $games->count(),
            'active_games' => $games->where('status', Game::STATUS_ACTIVE)->count(),
            'paused_games' => $games->where('status', Game::STATUS_PAUSED)->count(),
            'completed_games' => $games->where('status', Game::STATUS_GAME_OVER)->count(),
            'total_revenue' => $games->sum(function ($game) {
                return $game->calculateTotalRevenue();
            }),
            'average_game_duration_days' => $games->avg(function ($game) {
                return $game->created_at->diffInDays($game->updated_at);
            }) ?? 0,
            'most_successful_game' => $this->getMostSuccessfulGame(),
        ];
    }

    public function getMostSuccessfulGame(): ?array
    {
        $bestGame = $this->games()
            ->selectRaw('games.*, (SELECT SUM(value) FROM projects WHERE projects.game_id = games.id AND projects.status = "completed") as total_revenue')
            ->orderBy('total_revenue', 'desc')
            ->first();

        if (!$bestGame || !$bestGame->total_revenue) {
            return null;
        }

        return [
            'id' => $bestGame->id,
            'name' => $bestGame->getDisplayName(),
            'total_revenue' => $bestGame->total_revenue,
            'created_at' => $bestGame->created_at,
        ];
    }

    public function getProfileCompletion(): int
    {
        $fields = ['name', 'email', 'email_verified_at'];
        $completed = 0;
        
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }
        
        return (int) (($completed / count($fields)) * 100);
    }

    public function isActivePlayer(): bool
    {
        return $this->games()->where('updated_at', '>=', now()->subDays(7))->exists();
    }

    public function getAccountAgeDays(): int
    {
        return $this->created_at->diffInDays(now());
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeActive($query, int $days = 30)
    {
        return $query->whereHas('games', function ($q) use ($days) {
            $q->where('updated_at', '>=', now()->subDays($days));
        });
    }

    public function getInitials(): string
    {
        $names = explode(' ', $this->name);
        $initials = '';
        
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        
        return substr($initials, 0, 2);
    }

    public function canCreateNewGame(): bool
    {
        $activeGamesCount = $this->activeGames()->count();
        return $activeGamesCount < 5; 
    }

    public function getMaxActiveGames(): int
    {
        return 5;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        
        unset($array['password'], $array['remember_token']);
        
        if (request()->routeIs('auth.me') || request()->routeIs('user.*')) {
            $array['profile_completion'] = $this->getProfileCompletion();
            $array['account_age_days'] = $this->getAccountAgeDays();
            $array['is_active_player'] = $this->isActivePlayer();
            $array['initials'] = $this->getInitials();
            $array['can_create_new_game'] = $this->canCreateNewGame();
            $array['max_active_games'] = $this->getMaxActiveGames();
        }
        
        return $array;
    }
}