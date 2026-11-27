<?php

namespace App\Http\Requests\Users;

use App\Data\Users\ProfileUpdateData;
use App\Enums\PermissionName;
use App\Models\Person;
use App\Rules\StrongPassword;
use App\Rules\UserRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionName::ProfileUpdate->value);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            ...UserRules::sometimes([...UserRules::names(), ...UserRules::birthDate()]),
            'password' => ['sometimes', 'required', 'string', new StrongPassword, 'confirmed'],
            'current_password' => ['required_with:password', 'current_password:sanctum'],
        ];
    }

    public function toData(): ProfileUpdateData
    {
        return new ProfileUpdateData(
            personAttributes: $this->safe()->only((new Person)->getFillable()),
            password: $this->filled('password') ? $this->string('password')->toString() : null,
        );
    }
}
