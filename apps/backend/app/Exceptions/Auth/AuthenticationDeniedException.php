<?php

namespace App\Exceptions\Auth;

use App\Enums\LoginResult;
use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationDeniedException extends ApiException
{
    public function __construct(public readonly LoginResult $result)
    {
        parent::__construct(match ($result) {
            LoginResult::InvalidCredentials => 'Las credenciales proporcionadas no son válidas.',
            LoginResult::AccountBlocked => 'La cuenta está bloqueada. Contacte a un administrador para desbloquearla.',
            LoginResult::AccountInactive => 'La cuenta está inactiva.',
            LoginResult::ActiveSession => 'Ya existe una sesión activa para este usuario. Cierre la sesión actual antes de iniciar una nueva.',
        });
    }

    public function status(): int
    {
        return match ($this->result) {
            LoginResult::InvalidCredentials => Response::HTTP_UNAUTHORIZED,
            LoginResult::AccountBlocked => Response::HTTP_LOCKED,
            LoginResult::AccountInactive => Response::HTTP_FORBIDDEN,
            LoginResult::ActiveSession => Response::HTTP_CONFLICT,
        };
    }

    public function errorCode(): string
    {
        return $this->result->value;
    }
}
