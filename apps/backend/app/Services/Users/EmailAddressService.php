<?php

namespace App\Services\Users;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class EmailAddressService
{
    public function localPart(string $firstName, string $firstSurname, ?string $secondSurname): string
    {
        $localPart = $this->initial($firstName)
            .$this->clean($firstSurname)
            .$this->initial($secondSurname ?? '');

        return $localPart === '' ? EMAIL_FALLBACK_LOCAL_PART : Str::substr($localPart, 0, EMAIL_LOCAL_PART_MAX_LENGTH);
    }

    public function nextAvailable(string $localPart): string
    {
        return DB::selectOne(
            'select fn_next_available_email(?, ?) as email',
            [$localPart, config('accounts.email_domain')],
        )->email;
    }

    public function isConflict(Throwable $exception): bool
    {
        return $exception instanceof UniqueConstraintViolationException
            && str_contains($exception->getMessage(), EMAIL_UNIQUE_INDEX);
    }

    private function initial(string $value): string
    {
        return Str::substr($this->clean($value), 0, 1);
    }

    private function clean(string $value): string
    {
        return Str::of($value)->ascii()->lower()->replaceMatches('/[^a-z0-9]/', '')->toString();
    }
}
