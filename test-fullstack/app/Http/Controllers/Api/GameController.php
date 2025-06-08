<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\game\CreateGameRequest;
use App\Http\Requests\game\UpdateGameRequest;
use App\Http\Requests\game\PauseGameRequest;
use App\Http\Requests\game\ResumeGameRequest;
use App\Http\Resources\GameResource;
use App\Http\Resources\DeveloperResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SalesPersonResource;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Carbon\Carbon;

class GameController extends Controller
{
    /**
     * 🎯 FIX RAPIDO: Lista partite senza GameCollection per ora
     */
    public function index(): JsonResponse
    {
        // Eager loading per performance
        $games = Game::with(['developers', 'salesPeople'])
            ->withCount([
                'developers', 
                'salesPeople',
                'projects as projects_completed' => function ($query) {
                    $query->where('status', 'completed');
                },
                'projects as projects_active' => function ($query) {
                    $query->where('status', 'in_progress');
                },
                'projects as projects_pending' => function ($query) {
                    $query->where('status', 'pending');
                }
            ])
            ->orderBy('updated_at', 'desc')
            ->get();
        
        // Calcola meta data
        $meta = [
            'total_games' => $games->count(),
            'active_games' => $games->where('status', Game::STATUS_ACTIVE)->count(),
            'paused_games' => $games->where('status', Game::STATUS_PAUSED)->count(),
            'completed_games' => $games->where('status', Game::STATUS_GAME_OVER)->count(),
        ];

        // 🎯 FIX: Trasforma manualmente i dati per ora
        $gamesData = $games->map(function ($game) {
            return [
                'id' => $game->id,
                'name' => $game->name ?? 'Partita del ' . $game->created_at->format('d/m/Y H:i'),
                'money' => [
                    'amount' => $game->money,
                    'formatted' => number_format($game->money, 2, ',', '.') . ' €',
                    'status_class' => $this->getMoneyStatusClass($game->money),
                ],
                'status' => $game->status,
                'status_label' => $this->getStatusLabel($game->status),
                'status_badge_class' => $this->getStatusBadgeClass($game->status),
                'team_size' => ($game->developers_count ?? 0) + ($game->sales_people_count ?? 0),
                'projects_completed' => $game->projects_completed_count ?? 0,
                'projects_active' => $game->projects_active_count ?? 0,
                'projects_pending' => $game->projects_pending_count ?? 0,
                'play_time_hours' => $this->calculatePlayTimeHours($game),
                'play_time_formatted' => $this->formatPlayTime($this->calculatePlayTimeHours($game)),
                'quick_stats' => [
                    'is_profitable' => $game->money > 0,
                    'has_active_projects' => ($game->projects_active_count ?? 0) > 0,
                    'team_needs_expansion' => (($game->developers_count ?? 0) + ($game->sales_people_count ?? 0)) < 3,
                ],
                'created_at' => $game->created_at->toISOString(),
                'updated_at' => $game->updated_at->toISOString(),
                'last_played' => $game->updated_at->diffForHumans(),
                'last_played_date' => $game->updated_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'data' => $gamesData,
            'meta' => $meta,
        ]);
    }

    // 🎯 Helper methods per trasformazione dati
    private function getMoneyStatusClass($money): string
    {
        if ($money < 0) return 'text-red-600';
        if ($money < 1000) return 'text-yellow-600';
        return 'text-green-600';
    }
    
    private function getStatusLabel($status): string
    {
        return match($status) {
            'active' => 'Attiva',
            'paused' => 'In Pausa',
            'game_over' => 'Terminata',
            default => 'Sconosciuto'
        };
    }
    
    private function getStatusBadgeClass($status): string
    {
        return match($status) {
            'active' => 'bg-green-100 text-green-800',
            'paused' => 'bg-yellow-100 text-yellow-800',
            'game_over' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-500'
        };
    }
    
    private function calculatePlayTimeHours($game): float
    {
        $totalSeconds = $game->updated_at->diffInSeconds($game->created_at);
        $playSeconds = $totalSeconds - ($game->offline_duration_seconds ?? 0);
        return round($playSeconds / 3600, 1);
    }
    
    private function formatPlayTime($hours): string
    {
        if ($hours < 1) {
            return round($hours * 60) . 'm';
        }
        return round($hours, 1) . 'h';
    }

    /**
     * Store method - con fix per creazione 
     */
    public function store(CreateGameRequest $request)
    {
        $gameData = $request->validatedWithDefaults();
        
        $game = Game::create($gameData);
        
        // 🎯 FIX: Carica le relazioni per la response
        $game->load(['developers', 'salesPeople']);
        $game->loadCount(['developers', 'salesPeople']);
        
        return response()->json([
            'success' => true,
            'data' => new GameResource($game),
            'message' => 'Partita creata con successo!'
        ], 201);
    }

    public function show(Game $game): JsonResponse
    {
        // Eager load completo
        $game->load([
            'developers',
            'salesPeople', 
            'projects' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'projectGenerations' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);
        
        return response()->json([
            'data' => new GameResource($game),
        ]);
    }

    public function update(UpdateGameRequest $request, Game $game): JsonResponse
    {
        $game->update($request->validated());

        return response()->json([
            'data' => new GameResource($game->fresh()),
            'message' => 'Partita aggiornata con successo.',
        ]);
    }

    public function destroy(Game $game): JsonResponse
    {
        $game->delete();

        return response()->json([
            'message' => 'Partita eliminata con successo.',
        ]);
    }

    public function status(Game $game): JsonResponse
    {
        $game->load(['developers', 'salesPeople', 'projects', 'projectGenerations']);
        
        $completedProjects = $game->projects()->where('status', 'completed')->get();
        $totalRevenue = $completedProjects->sum('value');
        $monthlyCosts = $this->calculateMonthlyCosts($game);
        $totalCosts = $monthlyCosts * ($game->created_at->diffInMonths(Carbon::now()) ?: 1);
        $profitMargin = $totalRevenue > 0 ? (($totalRevenue - $totalCosts) / $totalRevenue) * 100 : 0;
        
        $efficiencyRating = 'Scarso';
        if ($profitMargin >= 50) $efficiencyRating = 'Eccellente';
        elseif ($profitMargin >= 30) $efficiencyRating = 'Buono';
        elseif ($profitMargin >= 10) $efficiencyRating = 'Sufficiente';

        $statistics = [
            'total_revenue' => $totalRevenue,
            'total_costs' => $totalCosts,
            'profit_margin' => round($profitMargin, 2),
            'efficiency_rating' => $efficiencyRating,
            'projects_per_month' => $completedProjects->count() / max(1, $game->created_at->diffInMonths(Carbon::now()) ?: 1),
            'average_project_value' => $completedProjects->count() > 0 ? $completedProjects->avg('value') : 0,
        ];

        return response()->json([
            'data' => new GameResource($game),
            'statistics' => $statistics,
        ]);
    }

    // Helper per calcolo costi
    private function calculateMonthlyCosts(Game $game): float
    {
        $developersCosts = $game->developers()->sum('salary');
        $salesCosts = $game->salesPeople()->sum('salary');
        $fixedCosts = 1500; // Costi fissi azienda
        
        return $developersCosts + $salesCosts + $fixedCosts;
    }

    public function pause(PauseGameRequest $request, Game $game): JsonResponse
    {
        if ($game->isGameOver()) {
            return response()->json([
                'message' => 'Non puoi mettere in pausa una partita già terminata.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($game->isPaused()) {
            return response()->json([
                'message' => 'La partita è già in pausa.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $game->pause();

        return response()->json([
            'data' => new GameResource($game->fresh()),
            'message' => 'Partita messa in pausa. A presto!',
        ]);
    }

    public function resume(ResumeGameRequest $request, Game $game): JsonResponse
    {
        if ($game->isGameOver()) {
            return response()->json([
                'message' => 'Non puoi riprendere una partita già terminata.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($game->isActive()) {
            return response()->json([
                'message' => 'La partita è già attiva.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $offlineSeconds = $request->getOfflineDurationSeconds();
        $game->resume();

        if ($offlineSeconds > 0) {
            $this->updateActiveTimersForOfflineTime($game, $offlineSeconds);
        }

        $offlineFormatted = $this->formatDuration($offlineSeconds);

        return response()->json([
            'data' => new GameResource($game->fresh()),
            'message' => 'Bentornato! La partita è ripresa.',
            'offline_time' => [
                'duration_seconds' => $offlineSeconds,
                'duration_formatted' => $offlineFormatted,
            ],
        ]);
    }

    private function updateActiveTimersForOfflineTime(Game $game, int $offlineSeconds): void
    {
        $game->projects()
            ->where('status', 'in_progress')
            ->each(function ($project) use ($offlineSeconds) {
                $project->update([
                    'estimated_completion_at' => $project->estimated_completion_at
                        ->addSeconds($offlineSeconds)
                ]);
            });

        $game->projectGenerations()
            ->where('status', 'in_progress')
            ->each(function ($generation) use ($offlineSeconds) {
                $generation->update([
                    'estimated_completion_at' => $generation->estimated_completion_at
                        ->addSeconds($offlineSeconds)
                ]);
            });
    }

    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' secondi';
        } elseif ($seconds < 3600) {
            $minutes = intval($seconds / 60);
            return $minutes . ' minuti';
        } else {
            $hours = intval($seconds / 3600);
            $remainingMinutes = intval(($seconds % 3600) / 60);
            
            if ($remainingMinutes > 0) {
                return $hours . ' ore e ' . $remainingMinutes . ' minuti';
            }
            
            return $hours . ' ore';
        }
    }
    public function developers(Game $game): JsonResponse
    {
        $developers = $game->developers()
            ->with(['currentProject'])
            ->orderBy('seniority', 'desc')
            ->get();

        $meta = [
            'total_developers' => $developers->count(),
            'available_developers' => $developers->where('is_busy', false)->count(),
            'busy_developers' => $developers->where('is_busy', true)->count(),
        ];

        return response()->json([
            'data' => DeveloperResource::collection($developers),
            'meta' => $meta,
        ]);
    }
    public function salesPeople(Game $game): JsonResponse
    {
        $salesPeople = $game->salesPeople()
            ->with(['currentGeneration'])
            ->orderBy('experience', 'desc')
            ->get();

        $meta = [
            'total_sales_people' => $salesPeople->count(),
            'available_sales_people' => $salesPeople->where('is_busy', false)->count(),
            'busy_sales_people' => $salesPeople->where('is_busy', true)->count(),
            'total_projects_generated' => $salesPeople->sum('projects_generated'),
            'total_value_generated' => $salesPeople->sum('total_value_generated'),
        ];

        return response()->json([
            'data' => SalesPersonResource::collection($salesPeople),
            'meta' => $meta,
        ]);
    }
}