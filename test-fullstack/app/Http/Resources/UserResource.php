<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserResource",
 *     title="User Resource",
 *     description="User resource representation",
 *     type="object",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="User unique identifier",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="User full name",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="User email address",
 *         example="john@example.com"
 *     ),
 *     @OA\Property(
 *         property="email_verified_at",
 *         type="string",
 *         format="date-time",
 *         description="Email verification timestamp",
 *         example="2025-06-03T14:30:00Z",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="User creation timestamp",
 *         example="2025-06-03T14:30:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="User last update timestamp",
 *         example="2025-06-03T14:30:00Z"
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         description="Resource metadata",
 *         @OA\Property(property="version", type="string", example="1.0"),
 *         @OA\Property(property="timestamp", type="string", format="date-time", example="2025-06-03T14:30:00Z")
 *     )
 * )
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}