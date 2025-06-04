<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateGameState
{

    public function handle(Request $request, Closure $next): Response
    {
        $game = $request->gameModel ?? $request->route('game');
        
        if ($game->status === 'game_over') {
            return response()->json([
                'error' => 'Game over - no actions allowed',
                'status' => 'game_over'
            ], 422);
        }
        
        if ($game->status === 'paused') {
            return response()->json([
                'error' => 'Game is paused',
                'status' => 'paused'
            ], 422);
        }
        
        return $next($request);
    }
}