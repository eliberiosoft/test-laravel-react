<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\SendPasswordResetLinkAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request, SendPasswordResetLinkAction $sendLink): JsonResponse
    {
        $sendLink->execute($request->email());

        return response()->json([
            'message' => 'Si el correo está registrado, recibirá un enlace para restablecer la contraseña.',
        ]);
    }
}
