<?php

namespace App\Exceptions;

use App\Http\Responses\ErrorResponse;
use Exception;
use Illuminate\Http\JsonResponse;

abstract class ApiException extends Exception
{
    abstract public function status(): int;

    abstract public function errorCode(): string;

    public function render(): JsonResponse
    {
        return ErrorResponse::make($this->getMessage(), $this->errorCode(), $this->status());
    }
}
