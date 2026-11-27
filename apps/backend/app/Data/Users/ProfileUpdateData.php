<?php

namespace App\Data\Users;

use SensitiveParameter;

final readonly class ProfileUpdateData
{
    /**
     * @param  array<string, mixed>  $personAttributes
     */
    public function __construct(
        public array $personAttributes,
        #[SensitiveParameter] public ?string $password,
    ) {}
}
