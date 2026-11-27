<?php

namespace App\Models;

use App\Enums\SessionEndReason;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[WithoutTimestamps]
#[Fillable(['user_id', 'token_id', 'ip_address', 'user_agent', 'started_at', 'expires_at', 'ended_at', 'end_reason'])]
class UserSession extends Model
{
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'ended_at' => 'datetime',
            'end_reason' => SessionEndReason::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[Scope]
    protected function open(Builder $query): void
    {
        $query->whereNull('ended_at');
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNull('ended_at')
            ->whereNotNull('token_id')
            ->where('expires_at', '>', now());
    }
}
