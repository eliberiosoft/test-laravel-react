<?php

namespace App\Http\Requests\Users;

use App\Data\Users\UserUpdateData;
use App\Enums\UserStatus;
use App\Models\Person;
use App\Models\User;
use App\Rules\UserRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->target());
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $target = $this->target();

        return [
            ...UserRules::sometimes([
                ...UserRules::names(),
                ...UserRules::identification($target->person_id),
                ...UserRules::birthDate(),
                ...UserRules::username($target->getKey()),
            ]),
            'status' => [
                'sometimes',
                'required',
                Rule::enum(UserStatus::class)->only([UserStatus::Active, UserStatus::Inactive]),
                Rule::prohibitedIf(fn (): bool => $target->isBlocked()),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.prohibited' => 'El usuario está bloqueado. Desbloquéelo antes de cambiar su estado.',
        ];
    }

    public function toData(): UserUpdateData
    {
        return new UserUpdateData(
            personAttributes: $this->safe()->only((new Person)->getFillable()),
            accountAttributes: $this->safe()->only(['username']),
            status: $this->enum('status', UserStatus::class),
        );
    }

    private function target(): User
    {
        return $this->route('user');
    }
}
