<?php

use App\Exceptions\BusinessRuleException;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {

        // La aplicación trabaja como API.
        // Si un usuario no está autenticado, no intenta
        // redirigirlo a una ruta web llamada "login".
        $middleware->redirectGuestsTo(fn () => null);

        // Alias para proteger rutas según el rol del usuario.
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {

        // 401 - Usuario no autenticado
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'message' => 'No autenticado.',
            ], 401);
        });

        // 403 - Usuario autenticado, pero sin autorización
        $exceptions->render(function (AuthorizationException $e) {
            return response()->json([
                'message' => 'No tiene permisos para acceder a este recurso.',
            ], 403);
        });
        // 403 - Acceso denegado por una Policy o Gate
        $exceptions->render(function (AccessDeniedHttpException $e) {
            return response()->json([
                'message' => 'No tiene permisos para acceder a este recurso.',
            ], 403);
        });

        // 409 - Regla de negocio incumplida
        $exceptions->render(function (BusinessRuleException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        });

        // 404 - Modelo o recurso no encontrado
        $exceptions->render(function (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Recurso no encontrado.',
            ], 404);
        });

        // 404 - Ruta o recurso no encontrado
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'message' => 'Recurso no encontrado.',
            ], 404);
        });

        // 422 - Error de validación
        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'message' => 'Los datos proporcionados no son válidos.',
                'errors' => $e->errors(),
            ], 422);
        });

        // 429 - Demasiados intentos
        $exceptions->render(function (TooManyRequestsHttpException $e) {
            return response()->json([
                'message' => 'Demasiados intentos. Intente nuevamente más tarde.',
            ], 429);
        });
    })

    ->create();