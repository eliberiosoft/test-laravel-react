<?php

namespace App\Exceptions\Auth;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

class InvalidPasswordResetTokenException extends ApiException
{
    public function __construct()
    {
        parent::__construct('El enlace de recuperación no es válido o expiró.');
    }

    public function status(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    public function errorCode(): string
    {
        return 'invalid_reset_token';
    }
}
