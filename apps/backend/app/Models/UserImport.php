<?php

namespace App\Models;

use App\Enums\ImportRowStatus;
use App\Enums\ImportStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'file_name', 'file_path', 'file_hash'])]
class UserImport extends Model
{
    protected $attributes = [
        'status' => ImportStatus::Pending->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => ImportStatus::class,
            'total_rows' => 'integer',
            'created_rows' => 'integer',
            'skipped_rows' => 'integer',
            'failed_rows' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(UserImportRow::class);
    }

    public static function counterFor(ImportRowStatus $status): string
    {
        return $status->value.'_rows';
    }
}
