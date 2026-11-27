<?php

namespace App\Models;

use App\Enums\LoginResult;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;

#[WithoutTimestamps]
#[Fillable(['user_id', 'identifier', 'result', 'ip_address', 'user_agent', 'attempted_at'])]
class LoginAttempt extends Model
{
    protected function casts(): array
    {
        return [
            'result' => LoginResult::class,
            'attempted_at' => 'datetime',
        ];
    }
}
