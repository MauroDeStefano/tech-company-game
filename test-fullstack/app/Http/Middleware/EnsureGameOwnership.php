<?php

namespace App\Http\Middleware;

use App\Models\Game;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGameOwnership
{
    public function handle(Request $request, Closure $next): Response
    {
        $game = $request->route('game');
        
        if (is_numeric($game)) {
            $game = Game::find($game);
        }
        
        if (!$game) {
            return response()->json(['error' => 'Game not found'], 404);
        }
        
        if ($game->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $request->merge(['gameModel' => $game]);
        
        return $next($request);
    }
}