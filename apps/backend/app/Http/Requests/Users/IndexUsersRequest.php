<?php

namespace App\Http\Requests\Users;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', User::class);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'filter' => ['sometimes', 'array'],
            'filter.role' => ['sometimes', Rule::enum(RoleName::class)],
            'filter.search' => ['sometimes', 'required', 'string', 'min:'.USER_SEARCH_MIN_LENGTH, 'max:'.USER_SEARCH_MAX_LENGTH],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.USERS_PER_PAGE_MAX],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'filter.search.required' => 'La búsqueda debe tener al menos '.USER_SEARCH_MIN_LENGTH.' caracteres.',
            'filter.search.min' => 'La búsqueda debe tener al menos '.USER_SEARCH_MIN_LENGTH.' caracteres.',
        ];
    }

    public function perPage(): int
    {
        return $this->integer('per_page', USERS_PER_PAGE_DEFAULT);
    }
}
