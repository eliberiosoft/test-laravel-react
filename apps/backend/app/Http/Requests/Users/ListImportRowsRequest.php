<?php

namespace App\Http\Requests\Users;

use App\Enums\ImportRowStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListImportRowsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->route('import'));
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'filter' => ['sometimes', 'array'],
            'filter.status' => ['sometimes', Rule::enum(ImportRowStatus::class)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.USERS_PER_PAGE_MAX],
        ];
    }

    public function perPage(): int
    {
        return $this->integer('per_page', IMPORT_ROWS_PER_PAGE_DEFAULT);
    }
}
