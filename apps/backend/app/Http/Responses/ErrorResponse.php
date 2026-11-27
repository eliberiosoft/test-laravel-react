<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

final class ErrorResponse
{
    /**
     * @param  array<string, string>  $headers
     */
    public static function make(string $message, string $code, int $status, array $headers = []): JsonResponse
    {
        return response()->json(['message' => $message, 'code' => $code], $status, $headers);
    }
}
