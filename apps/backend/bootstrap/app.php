<?php

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiInfoController;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Responses\ErrorResponse;
use App\Services\Auth\SessionService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::get('/', ApiInfoController::class)->name('api.info');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(ForceJsonResponse::class);
        $middleware->alias([
            'active.user' => EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport(ApiException::class);

        $exceptions->render(fn (AuthenticationException $exception, Request $request) => app(SessionService::class)->isExpiredToken($request->bearerToken())
            ? ErrorResponse::make('La sesión expiró. Inicie sesión nuevamente.', 'session_expired', Response::HTTP_UNAUTHORIZED)
            : ErrorResponse::make('No autenticado.', 'unauthenticated', Response::HTTP_UNAUTHORIZED));

        $exceptions->render(fn (AccessDeniedHttpException $exception) => ErrorResponse::make(
            'No tiene permiso para realizar esta acción.', 'forbidden', Response::HTTP_FORBIDDEN,
        ));

        $exceptions->render(fn (NotFoundHttpException $exception) => ErrorResponse::make(
            'Recurso no encontrado.', 'not_found', Response::HTTP_NOT_FOUND,
        ));

        $exceptions->render(fn (ThrottleRequestsException $exception) => ErrorResponse::make(
            'Demasiados intentos. Intente nuevamente más tarde.', 'too_many_requests', Response::HTTP_TOO_MANY_REQUESTS, $exception->getHeaders(),
        ));

        $exceptions->render(fn (UniqueConstraintViolationException $exception) => ErrorResponse::make(
            'El recurso ya existe.', 'duplicate_resource', Response::HTTP_CONFLICT,
        ));
    })->create();
