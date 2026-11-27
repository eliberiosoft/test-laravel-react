<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Username implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match(USERNAME_PATTERN, $value) !== 1) {
            $fail('El usuario debe tener entre 8 y 20 caracteres alfanuméricos, con al menos una mayúscula y un número.');
        }
    }
}
