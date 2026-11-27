<?php

namespace App\Exceptions\Users;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

class UserIsBlockedException extends ApiException
{
    public function __construct()
    {
        parent::__construct('El usuario está bloqueado. Desbloquéelo antes de cambiar su estado.');
    }

    public function status(): int
    {
        return Response::HTTP_CONFLICT;
    }

    public function errorCode(): string
    {
        return 'user_blocked';
    }
}
