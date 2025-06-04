<?php
// app/Http/Controllers/Controller.php (controller base - aggiungi questo)

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Tech Company Game API",
 *      description="API documentation for the Tech Company Management Game",
 *      @OA\Contact(
 *          email="admin@techcompanygame.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="https://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Local Development Server"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="sanctum",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="Token",
 *      description="Laravel Sanctum token authentication"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for user authentication"
 * )
 *
 * @OA\Tag(
 *     name="Games",
 *     description="API Endpoints for game management"
 * )
 *
 * @OA\Tag(
 *     name="Production",
 *     description="API Endpoints for production management"
 * )
 *
 * @OA\Tag(
 *     name="Sales",
 *     description="API Endpoints for sales management"
 * )
 *
 * @OA\Tag(
 *     name="HR",
 *     description="API Endpoints for human resources"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}