<?php

namespace App\Exceptions\Users;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

class UserNotBlockedException extends ApiException
{
    public function __construct()
    {
        parent::__construct('El usuario no está bloqueado.');
    }

    public function status(): int
    {
        return Response::HTTP_CONFLICT;
    }

    public function errorCode(): string
    {
        return 'user_not_blocked';
    }
}
