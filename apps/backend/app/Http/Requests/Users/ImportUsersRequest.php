<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ImportUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('import', User::class);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'extensions:'.implode(',', IMPORT_EXTENSIONS), 'mimes:csv,txt,xlsx', 'max:'.IMPORT_MAX_FILE_KB],
        ];
    }
}
