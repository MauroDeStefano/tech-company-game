<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateGameState
{
    /**
     * Handle an incoming request per validare lo stato del gioco
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 🎯 RECUPERA: Game dal middleware precedente o dalla route
        $game = $request->gameModel ?? $request->route('game');
        
        // Se il game non è stato ancora risolto
        if (!$game) {
            return response()->json([
                'error' => 'Game not found',
                'message' => 'Gioco non trovato per validazione stato.'
            ], 404);
        }
        
        // 🎯 VERIFICA: Se il gioco è in stato game_over
        if ($game->status === 'game_over') {
            return response()->json([
                'error' => 'Game over',
                'message' => 'Questa partita è terminata. Non puoi più effettuare azioni.',
                'status' => 'game_over',
                'game_id' => $game->id
            ], 422);
        }
        
        // 🎯 AVVISO: Se il gioco è in pausa (alcune azioni potrebbero essere limitate)
        if ($game->status === 'paused') {
            // Per ora permettiamo le azioni anche se in pausa
            // Potresti voler bloccare alcune azioni specifiche qui
            $request->merge(['game_is_paused' => true]);
        }
        
        // 🎯 INFO: Stato attivo - tutto OK
        if ($game->status === 'active') {
            $request->merge(['game_is_active' => true]);
        }
        
        return $next($request);
    }
}