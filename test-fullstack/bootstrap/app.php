<?php

use App\Http\Middleware\EnsureGameOwnership;
use App\Http\Middleware\ValidateGameState;
use App\Http\Middleware\ThrottleGameActions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',  // 🎯 Questo caricherà i tuoi file modulari
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 🎯 CORREZIONE: Registrazione middleware custom per il tuo gioco
        $middleware->alias([
            'game.ownership' => EnsureGameOwnership::class,
            'game.state' => ValidateGameState::class,
            'game.throttle' => ThrottleGameActions::class,
        ]);

        // 🎯 OPZIONALE: Rate limiting personalizzato per le API
        $middleware->api(append: [
            // Middleware globali per tutte le API se necessario
        ]);

        // 🎯 OPZIONALE: Gruppi di middleware predefiniti
        $middleware->appendToGroup('game-protected', [
            'auth:sanctum',
            'game.ownership',
            'game.state',
        ]);

        $middleware->appendToGroup('game-actions', [
            'auth:sanctum',
            'game.ownership', 
            'game.state',
            'game.throttle:10,1',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 🎯 Gestione eccezioni personalizzate per il gioco
        // $exceptions->report(function (GameException $e) {
        //     Log::error('Game Error: ' . $e->getMessage(), ['game_id' => $e->gameId]);
        // });
    })
    ->create();