<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ThrottleGameActions
{

    public function handle(Request $request, Closure $next, int $maxAttempts = 10, int $decayMinutes = 1): Response
    {
        $key = 'game_actions:' . $request->user()->id;
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            return response()->json([
                'error' => 'Too many actions. Please wait before trying again.',
                'retry_after' => $decayMinutes * 60
            ], 429);
        }
        
        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));
        
        return $next($request);
    }
}