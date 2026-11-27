<?php

namespace App\Http\Requests\Auth;

use App\Data\Auth\LoginCredentials;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            LOGIN_FIELD => ['required', 'string', 'max:'.LOGIN_IDENTIFIER_MAX_LENGTH],
            'password' => ['required', 'string', 'max:'.LOGIN_PASSWORD_MAX_LENGTH],
        ];
    }

    public function toCredentials(): LoginCredentials
    {
        $userAgent = $this->userAgent();

        return new LoginCredentials(
            login: $this->string(LOGIN_FIELD)->toString(),
            password: $this->string('password')->toString(),
            ipAddress: $this->ip(),
            userAgent: $userAgent === null ? null : Str::substr($userAgent, 0, USER_AGENT_MAX_LENGTH),
        );
    }
}
