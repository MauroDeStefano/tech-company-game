<?php

/**
 * @OA\Info(
 *     title="Tech Company Game API",
 *     version="1.0.0",
 *     description="API Documentation for Tech Company Game"
 * )
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Development Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer"
 * )
 */

require __DIR__.'/api/auth.php';
require __DIR__.'/api/users.php';
require __DIR__.'/api/games.php';