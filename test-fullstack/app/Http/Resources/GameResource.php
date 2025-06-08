<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 🎯 GameResource - Single Game Resource per show, create, update
 */
class GameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'money' => [
                'amount' => $this->money,
                'formatted' => number_format($this->money, 2, ',', '.') . ' €',
            ],
            'status' => $this->status,
            'is_active' => $this->isActive(),
            'is_paused' => $this->isPaused(),
            'is_game_over' => $this->isGameOver(),
            
            // 🎯 CORREZIONE: Usa metodi sicuri per calcolo costi
            'costs' => [
                'monthly' => $this->calculateMonthlyCosts(),
                'monthly_formatted' => number_format($this->calculateMonthlyCosts(), 2, ',', '.') . ' €',
            ],
            
            // 🎯 CORREZIONE: Team info con controllo esistenza relazioni
            'team' => [
                'developers_count' => $this->developers()->count(),
                'sales_people_count' => $this->salesPeople()->count(),
                'total_size' => $this->developers()->count() + $this->salesPeople()->count(),
            ],
            
            // 🎯 CORREZIONE: Projects info con conteggi diretti
            'projects' => [
                'active_count' => $this->projects()->where('status', 'in_progress')->count(),
                'pending_count' => $this->projects()->where('status', 'pending')->count(),
                'completed_count' => $this->projects()->where('status', 'completed')->count(),
                'total_count' => $this->projects()->count(),
            ],
            
            // 🎯 CORREZIONE: Relazioni dettagliate solo se richieste
            'developers' => $this->whenLoaded('developers', function () {
                return $this->developers->map(function ($developer) {
                    return [
                        'id' => $developer->id,
                        'name' => $developer->name,
                        'seniority' => $developer->seniority ?? 1,
                        'is_busy' => $developer->is_busy ?? false,
                        'salary' => $developer->salary ?? 2000,
                    ];
                });
            }),
            
            'sales_people' => $this->whenLoaded('salesPeople', function () {
                return $this->salesPeople->map(function ($salesPerson) {
                    return [
                        'id' => $salesPerson->id,
                        'name' => $salesPerson->name,
                        'experience' => $salesPerson->experience ?? 1,
                        'is_busy' => $salesPerson->is_busy ?? false,
                        'salary' => $salesPerson->salary ?? 1800,
                    ];
                });
            }),
            
            'recent_projects' => $this->whenLoaded('projects', function () {
                return $this->projects->take(5)->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'complexity' => $project->complexity ?? 1,
                        'value' => $project->value ?? 1000,
                        'status' => $project->status ?? 'pending',
                        'created_at' => $project->created_at->toISOString(),
                    ];
                });
            }),
            
            // 🎯 CORREZIONE: Timing info sicuro
            'timing' => [
                'total_offline_hours' => round(($this->offline_duration_seconds ?? 0) / 3600, 2),
                'paused_at' => $this->paused_at?->toISOString(),
                'resumed_at' => $this->resumed_at?->toISOString(),
                'play_time_hours' => $this->calculatePlayTimeHours(),
            ],
            
            // Setup and metadata
            'setup' => [
                'initial_setup_completed' => $this->initial_setup_completed ?? true,
            ],
            'notes' => $this->notes,
            
            // Timestamps
            'timestamps' => [
                'created_at' => $this->created_at->toISOString(),
                'updated_at' => $this->updated_at->toISOString(),
                'last_played' => $this->updated_at->diffForHumans(),
            ],
        ];
    }
    
    /**
     * 🎯 AGGIUNTA: Helper method per calcolare ore di gioco sicuro
     */
    private function calculatePlayTimeHours(): float
    {
        try {
            $totalSeconds = $this->updated_at->diffInSeconds($this->created_at);
            $playSeconds = $totalSeconds - ($this->offline_duration_seconds ?? 0);
            return round($playSeconds / 3600, 1);
        } catch (\Exception $e) {
            return 0.0;
        }
    }
}