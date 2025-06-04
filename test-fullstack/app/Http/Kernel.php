<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{

    protected $middleware = [
    ];

    protected $middlewareGroups = [
        'web' => [
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $middlewareAliases = [
        'game.ownership' => \App\Http\Middleware\EnsureGameOwnership::class,
        'game.state' => \App\Http\Middleware\ValidateGameState::class,
        'game.throttle' => \App\Http\Middleware\ThrottleGameActions::class,
    ];
}
