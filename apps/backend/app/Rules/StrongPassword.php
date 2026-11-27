<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('La contraseña no es válida.');

            return;
        }

        if (Str::length($value) < PASSWORD_MIN_LENGTH) {
            $fail('La contraseña debe tener al menos '.PASSWORD_MIN_LENGTH.' caracteres.');
        }

        if (preg_match('/\p{Lu}/u', $value) !== 1) {
            $fail('La contraseña debe incluir al menos una letra mayúscula.');
        }

        if (preg_match('/[^\p{L}\p{N}\s]/u', $value) !== 1) {
            $fail('La contraseña debe incluir al menos un signo.');
        }

        if (preg_match('/\s/u', $value) === 1) {
            $fail('La contraseña no puede contener espacios.');
        }
    }
}
