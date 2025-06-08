<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\game\CreateGameRequest;
use App\Http\Requests\game\UpdateGameRequest;
use App\Http\Requests\game\PauseGameRequest;
use App\Http\Requests\game\ResumeGameRequest;
use App\Http\Resources\GameResource;
use App\Http\Resources\GameCollection;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Carbon\Carbon;

/**
 * @OA\Info(
 *     title="Tech Company Game API",
 *     version="1.0.0",
 *     description="API per il videogioco gestionale Software House Manager. Gestisce partite, sviluppatori, commerciali e progetti.",
 *     @OA\Contact(
 *         email="support@techcompanygame.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Server di sviluppo locale"
 * )
 * 
 * @OA\Tag(
 *     name="Games",
 *     description="Gestione delle partite"
 * )
 */
class GameController extends Controller
{
    /**
     * @OA\Get(
     *     path="/games",
     *     summary="Lista tutte le partite salvate",
     *     description="Restituisce l'elenco di tutte le partite create, ordinate per data di ultimo accesso",
     *     tags={"Games"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista partite recuperata con successo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="La mia software house"),
     *                 @OA\Property(property="money", type="object",
     *                     @OA\Property(property="amount", type="number", format="float", example=5000.00),
     *                     @OA\Property(property="formatted", type="string", example="5.000,00 €")
     *                 ),
     *                 @OA\Property(property="status", type="string", enum={"active", "paused", "game_over"}, example="active"),
     *                 @OA\Property(property="team_size", type="integer", example=5),
     *                 @OA\Property(property="projects_completed", type="integer", example=12),
     *                 @OA\Property(property="play_time_hours", type="number", format="float", example=2.5),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="last_played", type="string", example="2 ore fa")
     *             )),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total_games", type="integer", example=5),
     *                 @OA\Property(property="active_games", type="integer", example=3),
     *                 @OA\Property(property="completed_games", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $games = Game::orderBy('updated_at', 'desc')->get();
        
        $meta = [
            'total_games' => $games->count(),
            'active_games' => $games->where('status', Game::STATUS_ACTIVE)->count(),
            'paused_games' => $games->where('status', Game::STATUS_PAUSED)->count(),
            'completed_games' => $games->where('status', Game::STATUS_GAME_OVER)->count(),
        ];

        return response()->json([
            'data' => GameCollection::collection($games),
            'meta' => $meta,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/games",
     *     summary="Crea una nuova partita",
     *     description="Inizializza una nuova partita con patrimonio iniziale di 5.000€, 1 developer e 1 commerciale",
     *     tags={"Games"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="La mia nuova software house", description="Nome opzionale per la partita"),
     *             @OA\Property(property="notes", type="string", example="Partita per testare nuove strategie", description="Note opzionali"),
     *             @OA\Property(property="initial_money", type="number", format="float", example=7000.00, description="Patrimonio iniziale personalizzato (1.000-50.000€)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Partita creata con successo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Game"),
     *             @OA\Property(property="message", type="string", example="Partita creata con successo! Buona fortuna!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errori di validazione",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="I dati forniti non sono validi."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */

    public function store(CreateGameRequest $request)
    {
        // Usa il metodo helper che hai creato
        $gameData = $request->validatedWithDefaults();
        
        $game = Game::create($gameData);
        
        return response()->json([
            'success' => true,
            'game' => $game,
            'message' => 'Partita creata con successo!'
        ], 201);
    }  

    /**
     * @OA\Get(
     *     path="/games/{id}",
     *     summary="Carica una partita specifica",
     *     description="Recupera tutti i dettagli di una partita, inclusi team, progetti e stato del gioco",
     *     tags={"Games"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID della partita",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Partita caricata con successo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Game")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Partita non trovata",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Partita non trovata.")
     *         )
     *     )
     * )
     */
    public function show(Game $game): JsonResponse
    {
        // Eager load delle relazioni per performance migliori
        $game->load(['developers', 'salesPeople', 'projects', 'projectGenerations']);
        
        return response()->json([
            'data' => new GameResource($game),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/games/{id}",
     *     summary="Aggiorna una partita",
     *     description="Aggiorna le informazioni di una partita esistente (nome, note, stato)",
     *     tags={"Games"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID della partita",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Software House Pro"),
     *             @OA\Property(property="notes", type="string", example="Strategia vincente trovata!"),
     *             @OA\Property(property="status", type="string", enum={"active", "paused", "game_over"}, example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Partita aggiornata con successo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Game"),
     *             @OA\Property(property="message", type="string", example="Partita aggiornata con successo.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Partita non trovata"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errori di validazione"
     *     )
     * )
     */
    public function update(UpdateGameRequest $request, Game $game): JsonResponse
    {
        $game->update($request->validated());

        return response()->json([
            'data' => new GameResource($game->fresh()),
            'message' => 'Partita aggiornata con successo.',
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/games/{id}",
     *     summary="Elimina una partita",
     *     description="Elimina definitivamente una partita e tutti i dati associati",
     *     tags={"Games"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID della partita",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Partita eliminata con successo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Partita eliminata con successo.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Partita non trovata"
     *     )
     * )
     */
    public function destroy(Game $game): JsonResponse
    {
        $game->delete();

        return response()->json([
            'message' => 'Partita eliminata con successo.',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/games/{id}/status",
     *     summary="Stato completo del gioco",
     *     description="Restituisce lo stato dettagliato della partita con tutte le statistiche e progressi",
     *     tags={"Games"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID della partita",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stato del gioco recuperato con successo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Game"),
     *             @OA\Property(property="statistics", type="object",
     *                 @OA\Property(property="total_revenue", type="number", format="float", example=25000.00),
     *                 @OA\Property(property="total_costs", type="number", format="float", example=18000.00),
     *                 @OA\Property(property="profit_margin", type="number", format="float", example=28.0),
     *                 @OA\Property(property="efficiency_rating", type="string", example="Eccellente")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Partita non trovata"
     *     )
     * )
     */
    public function status(Game $game): JsonResponse
    {
        $game->load(['developers', 'salesPeople', 'projects', 'projectGenerations']);
        
        $completedProjects = $game->projects()->where('status', 'completed')->get();
        $totalRevenue = $completedProjects->sum('value');
        $totalCosts = $game->calculateMonthlyCosts() * ($game->created_at->diffInMonths(Carbon::now()) ?: 1);
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

    /**
     * @OA\Post(
     *     path="/games/{id}/pause",
     *     summary="Mette in pausa la partita",
     *     description="Ferma il tempo di gioco e salva il timestamp di pausa per calcolare il tempo offline",
     *     tags={"Games"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID della partita",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="paused_at", type="integer", example=1640995200, description="Timestamp Unix opzionale")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Partita messa in pausa con successo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Game"),
     *             @OA\Property(property="message", type="string", example="Partita messa in pausa. A presto!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="La partita è già in pausa o è game over",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Non puoi mettere in pausa una partita già terminata.")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/games/{id}/resume",
     *     summary="Riprende la partita",
     *     description="Riattiva il gioco e calcola il tempo trascorso offline per aggiustare i timer",
     *     tags={"Games"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID della partita",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="paused_at", type="integer", example=1640995200, description="Timestamp quando è stata messa in pausa"),
     *             @OA\Property(property="offline_duration", type="integer", example=3600000, description="Durata offline in millisecondi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Partita ripresa con successo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Game"),
     *             @OA\Property(property="message", type="string", example="Bentornato! La partita è ripresa."),
     *             @OA\Property(property="offline_time", type="object",
     *                 @OA\Property(property="duration_seconds", type="integer", example=3600),
     *                 @OA\Property(property="duration_formatted", type="string", example="1 ora")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="La partita non è in pausa o è game over"
     *     )
     * )
     */
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
}