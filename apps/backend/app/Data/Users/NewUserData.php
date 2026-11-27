<?php

namespace App\Data\Users;

use SensitiveParameter;

final readonly class NewUserData
{
    public function __construct(
        public string $firstName,
        public ?string $middleName,
        public string $firstSurname,
        public ?string $secondSurname,
        public string $identification,
        public ?string $birthDate,
        public string $username,
        #[SensitiveParameter] public string $password,
    ) {}

    /**
     * @return array<string, string|null>
     */
    public function personAttributes(): array
    {
        return [
            'first_name' => $this->firstName,
            'middle_name' => $this->middleName,
            'first_surname' => $this->firstSurname,
            'second_surname' => $this->secondSurname,
            'identification' => $this->identification,
            'birth_date' => $this->birthDate,
        ];
    }
}
