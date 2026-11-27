<?php

namespace App\Http\Resources;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Person
 */
class PersonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'first_surname' => $this->first_surname,
            'second_surname' => $this->second_surname,
            'full_name' => $this->full_name,
            'identification' => $this->identification,
            'birth_date' => $this->birth_date?->toDateString(),
        ];
    }
}
