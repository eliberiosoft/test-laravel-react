<?php

namespace App\Http\Requests\Auth;

use App\Data\Auth\PasswordResetData;
use App\Rules\StrongPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:'.LOGIN_IDENTIFIER_MAX_LENGTH],
            'password' => ['required', 'string', new StrongPassword, 'confirmed'],
        ];
    }

    public function toData(): PasswordResetData
    {
        return new PasswordResetData(
            email: Str::lower($this->string('email')->trim()->toString()),
            token: $this->string('token')->toString(),
            password: $this->string('password')->toString(),
        );
    }
}
