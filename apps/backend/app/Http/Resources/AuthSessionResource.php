<?php

namespace App\Http\Resources;

use App\Data\Auth\IssuedSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property IssuedSession $resource
 */
class AuthSessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->resource->plainTextToken,
            'token_type' => AUTH_TOKEN_TYPE,
            'expires_at' => $this->resource->expiresAt->toIso8601String(),
            'user' => UserResource::make($this->resource->user->loadMissing(['person', 'roles.permissions', 'permissions'])),
        ];
    }
}
