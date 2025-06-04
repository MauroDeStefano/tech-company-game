<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GameController;

Route::middleware('auth:sanctum')->prefix('games')->group(function () {
    Route::get('/', [GameController::class, 'index']);
    Route::post('/', [GameController::class, 'store']);

    Route::middleware(['game.ownership', 'game.state'])->group(function () {
        Route::get('/{game}', [GameController::class, 'show']);
        Route::put('/{game}', [GameController::class, 'update']);
        Route::delete('/{game}', [GameController::class, 'destroy']);
        Route::get('/{game}/status', [GameController::class, 'status']);

        Route::middleware('game.throttle:15,1')->group(function () {
            Route::post('/{game}/tick', [GameController::class, 'tick']);
            Route::post('/{game}/projects/{project}/assign', [GameController::class, 'assignProject']);
            Route::post('/{game}/sales-people/{salesPerson}/generate-project', [GameController::class, 'generateProject']);
            Route::post('/{game}/hire/developer/{developerId}', [GameController::class, 'hireDeveloper']);
            Route::post('/{game}/hire/sales-person/{salesPersonId}', [GameController::class, 'hireSalesPerson']);
        });

        Route::get('/{game}/developers', [GameController::class, 'developers']);
        Route::get('/{game}/projects/pending', [GameController::class, 'pendingProjects']);
        Route::get('/{game}/sales-people', [GameController::class, 'salesPeople']);
        Route::get('/{game}/market/developers', [GameController::class, 'marketDevelopers']);
        Route::get('/{game}/market/sales-people', [GameController::class, 'marketSalesPeople']);
    });
});
