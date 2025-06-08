<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignProjectRequest;
use App\Http\Requests\CompleteProjectRequest;
use App\Http\Resources\DeveloperResource;
use App\Http\Resources\ProjectResource;
use App\Models\Game;
use App\Models\Project;
use App\Models\Developer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Production",
 *     description="API per la gestione della produzione (sviluppatori e progetti)"
 * )
 */
class ProductionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/games/{game}/developers",
     *     summary="Lista sviluppatori del gioco",
     *     description="Restituisce tutti gli sviluppatori del gioco con il loro stato attuale",
     *     tags={"Production"},
     *     @OA\Parameter(
     *         name="game",
     *         in="path",
     *         required=true,
     *         description="ID del gioco",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista sviluppatori recuperata con successo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Developer")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total_developers", type="integer"),
     *                 @OA\Property(property="available_developers", type="integer"),
     *                 @OA\Property(property="busy_developers", type="integer")
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/games/{game}/projects/pending",
     *     summary="Lista progetti in attesa",
     *     description="Restituisce tutti i progetti in attesa di assegnazione",
     *     tags={"Production"},
     *     @OA\Parameter(
     *         name="game",
     *         in="path",
     *         required=true,
     *         description="ID del gioco",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista progetti in attesa recuperata con successo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Project")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total_pending", type="integer"),
     *                 @OA\Property(property="total_value", type="number", format="float"),
     *                 @OA\Property(property="complexity_breakdown", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function pendingProjects(Game $game): JsonResponse
    {
        $projects = $game->projects()
            ->pending()
            ->with(['createdBySalesPerson'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Statistiche sui progetti
        $complexityBreakdown = $projects->groupBy('complexity')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_value' => $group->sum('value'),
            ];
        });

        $meta = [
            'total_pending' => $projects->count(),
            'total_value' => $projects->sum('value'),
            'complexity_breakdown' => $complexityBreakdown,
        ];

        return response()->json([
            'data' => ProjectResource::collection($projects),
            'meta' => $meta,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/games/{game}/projects/active",
     *     summary="Lista progetti in corso",
     *     description="Restituisce tutti i progetti attualmente in corso con i relativi progressi",
     *     tags={"Production"},
     *     @OA\Parameter(
     *         name="game",
     *         in="path",
     *         required=true,
     *         description="ID del gioco",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista progetti attivi recuperata con successo"
     *     )
     * )
     */
    public function activeProjects(Game $game): JsonResponse
    {
        $projects = $game->projects()
            ->inProgress()
            ->with(['developer', 'createdBySalesPerson'])
            ->orderBy('estimated_completion_at', 'asc')
            ->get();

        // Calcola progressi per ogni progetto
        $projectsWithProgress = $projects->map(function ($project) {
            $projectArray = $project->toArray();
            $projectArray['progress_percentage'] = $project->calculateCurrentProgress();
            $projectArray['is_nearly_complete'] = $project->isReadyForCompletion();
            return $projectArray;
        });

        $meta = [
            'total_active' => $projects->count(),
            'total_value' => $projects->sum('value'),
            'nearly_complete' => $projects->filter(fn($p) => $p->isReadyForCompletion())->count(),
        ];

        return response()->json([
            'data' => $projectsWithProgress,
            'meta' => $meta,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/games/{game}/projects/{project}/assign",
     *     summary="Assegna un progetto a uno sviluppatore",
     *     description="Assegna un progetto in attesa a uno sviluppatore disponibile",
     *     tags={"Production"},
     *     @OA\Parameter(
     *         name="game",
     *         in="path",
     *         required=true,
     *         description="ID del gioco",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         required=true,
     *         description="ID del progetto",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="developer_id", type="integer", example=1, description="ID dello sviluppatore da assegnare")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Progetto assegnato con successo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Progetto assegnato con successo!"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="project", ref="#/components/schemas/Project"),
     *                 @OA\Property(property="developer", ref="#/components/schemas/Developer"),
     *                 @OA\Property(property="estimated_completion", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errore di validazione o assegnazione non possibile"
     *     )
     * )
     */
    public function assignProject(AssignProjectRequest $request, Game $game, Project $project): JsonResponse
    {
        if ($project->game_id !== $game->id) {
            return response()->json([
                'message' => 'Progetto non trovato in questo gioco.',
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$project->isPending()) {
            return response()->json([
                'message' => 'Il progetto non è in attesa di assegnazione.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $developer = Developer::find($request->validated()['developer_id']);

        if (!$developer || $developer->game_id !== $game->id) {
            return response()->json([
                'message' => 'Sviluppatore non trovato in questo gioco.',
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$developer->isAvailable()) {
            return response()->json([
                'message' => 'Lo sviluppatore è già occupato su un altro progetto.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$developer->canHandleComplexity($project->complexity)) {
            return response()->json([
                'message' => 'Lo sviluppatore non ha la seniority necessaria per questo progetto.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $success = $project->assignToDeveloper($developer);

        if (!$success) {
            return response()->json([
                'message' => 'Errore durante l\'assegnazione del progetto.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => 'Progetto assegnato con successo!',
            'data' => [
                'project' => new ProjectResource($project->fresh(['developer'])),
                'developer' => new DeveloperResource($developer->fresh()),
                'estimated_completion' => $project->estimated_completion_at,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/projects/{project}/complete",
     *     summary="Completa un progetto",
     *     description="Segna un progetto come completato se il tempo è scaduto",
     *     tags={"Production"},
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         required=true,
     *         description="ID del progetto",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Progetto completato con successo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="completed", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Progetto completato! +2500€"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="project", ref="#/components/schemas/Project"),
     *                 @OA\Property(property="money_earned", type="number", format="float"),
     *                 @OA\Property(property="new_game_balance", type="number", format="float"),
     *                 @OA\Property(property="developer_freed", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Progetto non pronto per il completamento",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="completed", type="boolean", example=false),
     *             @OA\Property(property="reason", type="string", example="too_early"),
     *             @OA\Property(property="seconds_remaining", type="integer")
     *         )
     *     )
     * )
     */
    public function completeProject(CompleteProjectRequest $request, Project $project): JsonResponse
    {
        if (!$project->isInProgress()) {
            return response()->json([
                'completed' => false,
                'reason' => 'not_in_progress',
                'message' => 'Il progetto non è in corso.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$project->isReadyForCompletion(30)) {
            return response()->json([
                'completed' => false,
                'reason' => 'too_early',
                'seconds_remaining' => $project->getSecondsRemaining(),
                'message' => 'Il progetto non è ancora pronto per il completamento.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $developer = $project->developer;
        $game = $project->game;
        $moneyEarned = $project->value;

        \DB::transaction(function () use ($project) {
            $project->complete();
        });

        return response()->json([
            'completed' => true,
            'message' => "Progetto completato! +{$moneyEarned}€",
            'data' => [
                'project' => new ProjectResource($project->fresh()),
                'money_earned' => $moneyEarned,
                'new_game_balance' => $game->fresh()->money,
                'developer_freed' => $developer->name,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/games/{game}/projects/{project}/unassign",
     *     summary="Annulla assegnazione progetto",
     *     description="Rimuove l'assegnazione di un progetto e libera lo sviluppatore",
     *     tags={"Production"},
     *     @OA\Response(
     *         response=200,
     *         description="Assegnazione annullata con successo"
     *     )
     * )
     */
    public function unassignProject(Game $game, Project $project): JsonResponse
    {
        if ($project->game_id !== $game->id) {
            return response()->json([
                'message' => 'Progetto non trovato in questo gioco.',
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$project->isInProgress()) {
            return response()->json([
                'message' => 'Il progetto non è in corso.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $developer = $project->developer;
        $success = $project->unassign();

        if (!$success) {
            return response()->json([
                'message' => 'Errore durante l\'annullamento dell\'assegnazione.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => 'Assegnazione annullata. Lo sviluppatore è ora disponibile.',
            'data' => [
                'project' => new ProjectResource($project->fresh()),
                'developer' => new DeveloperResource($developer->fresh()),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/games/{game}/production/overview",
     *     summary="Panoramica produzione",
     *     description="Restituisce una panoramica completa dello stato della produzione",
     *     tags={"Production"},
     *     @OA\Response(
     *         response=200,
     *         description="Panoramica produzione recuperata con successo"
     *     )
     * )
     */
    public function overview(Game $game): JsonResponse
    {
        $developers = $game->developers;
        $projects = $game->projects;

        $overview = [
            'developers' => [
                'total' => $developers->count(),
                'available' => $developers->where('is_busy', false)->count(),
                'busy' => $developers->where('is_busy', true)->count(), 
                'average_seniority' => $developers->avg('seniority'),
                'total_monthly_costs' => $developers->sum('monthly_salary'),
            ],
            'projects' => [
                'pending' => $projects->where('status', 'pending')->count(),
                'in_progress' => $projects->where('status', 'in_progress')->count(),
                'completed' => $projects->where('status', 'completed')->count(),
                'failed' => $projects->where('status', 'failed')->count(),
                'total_value_pending' => $projects->where('status', 'pending')->sum('value'),
                'total_value_in_progress' => $projects->where('status', 'in_progress')->sum('value'),
                'total_revenue_earned' => $projects->where('status', 'completed')->sum('value'),
            ],
            'efficiency' => [
                'projects_per_developer' => $developers->count() > 0 
                    ? $projects->where('status', 'completed')->count() / $developers->count() 
                    : 0,
                'average_project_value' => $projects->where('status', 'completed')->avg('value') ?? 0,
                'completion_rate' => $projects->count() > 0 
                    ? ($projects->where('status', 'completed')->count() / $projects->count()) * 100 
                    : 0,
            ],
        ];

        return response()->json([
            'data' => $overview,
        ]);
    }
}