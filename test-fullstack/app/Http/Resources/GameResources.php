<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'money' => [
                'amount' => $this->money,
                'formatted' => number_format($this->money, 2) . ' €',
            ],
            'status' => $this->status,
            'is_active' => $this->isActive(),
            'is_paused' => $this->isPaused(),
            'is_game_over' => $this->isGameOver(),
            'costs' => [
                'monthly' => $this->calculateMonthlyCosts(),
                'monthly_formatted' => number_format($this->calculateMonthlyCosts(), 2) . ' €',
            ],
            'team' => [
                'developers_count' => $this->developers()->count(),
                'sales_people_count' => $this->salesPeople()->count(),
            ],
            'projects' => [
                'active_count' => $this->projects()->where('status', 'in_progress')->count(),
                'pending_count' => $this->projects()->where('status', 'pending')->count(),
                'completed_count' => $this->projects()->where('status', 'completed')->count(),
            ],
            'timing' => [
                'total_offline_hours' => round($this->offline_duration_seconds / 3600, 2),
                'paused_at' => $this->paused_at?->toISOString(),
                'resumed_at' => $this->resumed_at?->toISOString(),
            ],
            'setup' => [
                'initial_setup_completed' => $this->initial_setup_completed,
            ],
            'notes' => $this->notes,
            'timestamps' => [
                'created_at' => $this->created_at->toISOString(),
                'updated_at' => $this->updated_at->toISOString(),
            ],
        ];
    }
}


class GameCollection extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name ?? 'Partita del ' . $this->created_at->format('d/m/Y H:i'),
            'money' => [
                'amount' => $this->money,
                'formatted' => number_format($this->money, 2) . ' €',
            ],
            'status' => $this->status,
            'team_size' => $this->developers()->count() + $this->salesPeople()->count(),
            'projects_completed' => $this->projects()->where('status', 'completed')->count(),
            'play_time_hours' => round(($this->updated_at->diffInSeconds($this->created_at) - $this->offline_duration_seconds) / 3600, 1),
            'created_at' => $this->created_at->toISOString(),
            'last_played' => $this->updated_at->diffForHumans(),
        ];
    }
}