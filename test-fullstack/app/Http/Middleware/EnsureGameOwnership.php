<?php

namespace App\Http\Middleware;

use App\Models\Game;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGameOwnership
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 🎯 CORREZIONE: Ottieni il game dalla route
        $game = $request->route('game');
        
        // Se il parametro è un ID, carica il model
        if (is_numeric($game)) {
            $game = Game::find($game);
        }
        
        // 🎯 VERIFICA: Game esiste
        if (!$game) {
            return response()->json([
                'error' => 'Game not found',
                'message' => 'La partita richiesta non esiste.'
            ], 404);
        }
        
        // 🎯 VERIFICA: User autenticato
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Accesso negato. Devi essere autenticato.'
            ], 401);
        }
        
        // 🎯 VERIFICA: Proprietà del gioco
        if ($game->user_id !== $user->id) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'Non hai il permesso di accedere a questa partita.'
            ], 403);
        }
        
        // 🎯 AGGIUNTA: Passa il game al request per uso nel controller
        $request->merge(['gameModel' => $game]);
        
        return $next($request);
    }
}