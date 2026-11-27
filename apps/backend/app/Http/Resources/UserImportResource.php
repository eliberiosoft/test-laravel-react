<?php

namespace App\Http\Resources;

use App\Models\UserImport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserImport
 */
class UserImportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'file_name' => $this->file_name,
            'summary' => [
                'total' => $this->total_rows,
                'created' => $this->created_rows,
                'skipped' => $this->skipped_rows,
                'failed' => $this->failed_rows,
            ],
            'error' => $this->error,
            'started_at' => $this->started_at?->toIso8601String(),
            'finished_at' => $this->finished_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
