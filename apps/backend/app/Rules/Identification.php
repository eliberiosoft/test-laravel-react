<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Identification implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match(IDENTIFICATION_PATTERN, $value) !== 1) {
            $fail('La identificación debe tener exactamente 10 dígitos.');

            return;
        }

        if (preg_match(IDENTIFICATION_REPEATED_DIGITS_PATTERN, $value) === 1) {
            $fail('La identificación no puede tener cuatro dígitos iguales consecutivos.');
        }
    }
}
