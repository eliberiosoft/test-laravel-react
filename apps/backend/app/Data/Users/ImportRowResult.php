<?php

namespace App\Data\Users;

use App\Enums\ImportRowStatus;

final readonly class ImportRowResult
{
    /**
     * @param  array<string, list<string>>  $errors
     */
    private function __construct(
        public int $line,
        public ImportRowStatus $status,
        public ?string $identification,
        public ?string $email = null,
        public array $errors = [],
    ) {}

    public static function created(int $line, string $identification, string $email): self
    {
        return new self($line, ImportRowStatus::Created, $identification, $email);
    }

    public static function skipped(int $line, ?string $identification, string $reason): self
    {
        return new self($line, ImportRowStatus::Skipped, $identification, errors: ['identification' => [$reason]]);
    }

    /**
     * @param  array<string, list<string>>  $errors
     */
    public static function failed(int $line, ?string $identification, array $errors): self
    {
        return new self($line, ImportRowStatus::Failed, $identification, errors: $errors);
    }
}
