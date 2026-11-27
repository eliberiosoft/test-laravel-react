<?php

namespace App\Http\Resources;

use App\Models\UserImportRow;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserImportRow
 */
class UserImportRowResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'line' => $this->line,
            'status' => $this->status->value,
            'identification' => $this->identification,
            'email' => $this->email,
            'errors' => (object) ($this->errors ?? []),
        ];
    }
}
