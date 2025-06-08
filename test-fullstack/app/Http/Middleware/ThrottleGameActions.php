<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ThrottleGameActions
{
    /**
     * Handle an incoming request con rate limiting personalizzato per azioni di gioco
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 10, int $decayMinutes = 1): Response
    {
        // 🎯 GENERA: Chiave unica per utente + gioco
        $user = $request->user();
        $game = $request->gameModel ?? $request->route('game');
        
        $key = 'game_actions:' . $user->id;
        if ($game) {
            $key .= ':' . (is_object($game) ? $game->id : $game);
        }
        
        // 🎯 VERIFICA: Tentativi attuali
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            $retryAfter = $decayMinutes * 60;
            
            return response()->json([
                'error' => 'Too many game actions',
                'message' => 'Troppe azioni di gioco. Riprova tra qualche istante.',
                'retry_after_seconds' => $retryAfter,
                'max_attempts' => $maxAttempts,
                'current_attempts' => $attempts
            ], 429);
        }
        
        // 🎯 INCREMENTA: Contatore tentativi
        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));
        
        // 🎯 HEADERS: Aggiungi info rate limiting alla response
        $response = $next($request);
        
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - $attempts - 1));
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($decayMinutes)->timestamp);
        
        return $response;
    }
}