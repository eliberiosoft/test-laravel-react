<?php

namespace App\Models;

use App\Enums\ImportRowStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;

#[WithoutTimestamps]
#[Fillable(['user_import_id', 'line', 'status', 'identification', 'email', 'errors'])]
class UserImportRow extends Model
{
    protected function casts(): array
    {
        return [
            'line' => 'integer',
            'status' => ImportRowStatus::class,
            'errors' => 'array',
        ];
    }
}
