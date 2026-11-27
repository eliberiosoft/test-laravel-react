<?php

namespace App\Http\Requests\Users;

use App\Data\Users\NewUserData;
use App\Models\User;
use App\Rules\StrongPassword;
use App\Rules\UserRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            ...UserRules::newAccount(),
            'password' => ['required', 'string', new StrongPassword],
        ];
    }

    public function toData(): NewUserData
    {
        return new NewUserData(
            firstName: $this->string('first_name')->trim()->toString(),
            middleName: $this->filled('middle_name') ? $this->string('middle_name')->trim()->toString() : null,
            firstSurname: $this->string('first_surname')->trim()->toString(),
            secondSurname: $this->filled('second_surname') ? $this->string('second_surname')->trim()->toString() : null,
            identification: $this->string('identification')->toString(),
            birthDate: $this->filled('birth_date') ? $this->date('birth_date')->toDateString() : null,
            username: $this->string('username')->toString(),
            password: $this->string('password')->toString(),
        );
    }
}
