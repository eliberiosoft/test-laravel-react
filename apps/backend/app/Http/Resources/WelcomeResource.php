<?php

namespace App\Http\Resources;

use App\Data\Users\WelcomeSummary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property WelcomeSummary $resource
 */
class WelcomeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $session = $this->resource->previousSession;

        return [
            'user' => UserResource::make($this->resource->user),
            'last_session' => $session === null ? null : [
                'started_at' => $session->started_at->toIso8601String(),
                'ended_at' => $session->ended_at?->toIso8601String(),
                'end_reason' => $session->end_reason?->value,
                'ip_address' => $session->ip_address,
            ],
            'failed_attempts' => [
                'since_last_session' => $this->resource->failedAttemptsSincePreviousSession,
                'last_attempt_at' => $this->resource->lastFailedAttemptAt?->toIso8601String(),
            ],
        ];
    }
}
