<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/auth/register",
     *      operationId="register",
     *      tags={"Authentication"},
     *      summary="Register a new user",
     *      description="Register a new user and return user info with token",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      description="User's full name",
     *                      example="Mario Rossi"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      format="email",
     *                      description="User's email address",
     *                      example="mario.rossi@example.com"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                      format="password",
     *                      description="User's password (min 8 characters)",
     *                      example="password123"
     *                  ),
     *                  @OA\Property(
     *                      property="password_confirmation",
     *                      type="string",
     *                      format="password",
     *                      description="Password confirmation",
     *                      example="password123"
     *                  ),
     *                  required={"name", "email", "password", "password_confirmation"}
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="User registered successfully",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="message", type="string", example="User registered successfully"),
     *                  @OA\Property(
     *                      property="user",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="Mario Rossi"),
     *                      @OA\Property(property="email", type="string", example="mario.rossi@example.com")
     *                  ),
     *                  @OA\Property(
     *                      property="token",
     *                      type="string",
     *                      example="1|eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="message", type="string", example="The given data was invalid."),
     *                  @OA\Property(
     *                      property="errors",
     *                      type="object",
     *                      @OA\Property(
     *                          property="email",
     *                          type="array",
     *                          @OA\Items(type="string", example="The email has already been taken.")
     *                      )
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createGameToken();

        return response()->json([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token->plainTextToken,
        ], 201);
    }

    /**
     * @OA\Post(
     *      path="/api/auth/login",
     *      operationId="login",
     *      tags={"Authentication"},
     *      summary="Login user",
     *      description="Login user and return user info with token",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      format="email",
     *                      description="User's email",
     *                      example="mario.rossi@example.com"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                      format="password",
     *                      description="User's password",
     *                      example="password123"
     *                  ),
     *                  required={"email", "password"}
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Login successful",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="message", type="string", example="Login successful"),
     *                  @OA\Property(
     *                      property="user",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="Mario Rossi"),
     *                      @OA\Property(property="email", type="string", example="mario.rossi@example.com")
     *                  ),
     *                  @OA\Property(
     *                      property="token",
     *                      type="string",
     *                      example="1|eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Invalid credentials",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="message", type="string", example="The given data was invalid."),
     *                  @OA\Property(
     *                      property="errors",
     *                      type="object",
     *                      @OA\Property(
     *                          property="email",
     *                          type="array",
     *                          @OA\Items(type="string", example="The provided credentials are incorrect.")
     *                      )
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createGameToken();

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token->plainTextToken,
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/auth/logout",
     *      operationId="logout",
     *      tags={"Authentication"},
     *      summary="Logout user",
     *      description="Logout current user (revoke current token)",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Logout successful",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="message", type="string", example="Logout successful")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="message", type="string", example="Unauthenticated.")
     *              )
     *          )
     *      )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful'
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/auth/me",
     *      operationId="me",
     *      tags={"Authentication"},
     *      summary="Get current user info",
     *      description="Get current authenticated user information",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Current user info",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="user",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="Mario Rossi"),
     *                      @OA\Property(property="email", type="string", example="mario.rossi@example.com")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="message", type="string", example="Unauthenticated.")
     *              )
     *          )
     *      )
     * )
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/auth/logout-all",
     *      operationId="logoutAll",
     *      tags={"Authentication"},
     *      summary="Logout from all devices",
     *      description="Revoke all tokens for current user",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Logged out from all devices",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="message", type="string", example="Logged out from all devices")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="message", type="string", example="Unauthenticated.")
     *              )
     *          )
     *      )
     * )
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices'
        ]);
    }
}