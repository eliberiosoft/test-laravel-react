<?php

namespace App\Rules;

use Illuminate\Validation\Rule;

final class UserRules
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function names(): array
    {
        $name = ['string', 'max:'.PERSON_NAME_MAX_LENGTH, 'regex:'.PERSON_NAME_PATTERN];

        return [
            'first_name' => ['required', ...$name],
            'middle_name' => ['nullable', ...$name],
            'first_surname' => ['required', ...$name],
            'second_surname' => ['nullable', ...$name],
        ];
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function identification(?int $ignorePersonId = null): array
    {
        return [
            'identification' => ['required', 'string', new Identification, Rule::unique('persons', 'identification')->ignore($ignorePersonId)],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function birthDate(): array
    {
        return [
            'birth_date' => ['nullable', 'date', 'before:today'],
        ];
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function username(?int $ignoreUserId = null): array
    {
        return [
            'username' => ['required', 'string', new Username, Rule::unique('users', 'username')->ignore($ignoreUserId)],
        ];
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function newAccount(): array
    {
        return [...self::names(), ...self::identification(), ...self::birthDate(), ...self::username()];
    }

    /**
     * @param  array<string, list<mixed>>  $rules
     * @return array<string, list<mixed>>
     */
    public static function sometimes(array $rules): array
    {
        return array_map(fn (array $fieldRules): array => ['sometimes', ...$fieldRules], $rules);
    }
}
