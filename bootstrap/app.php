<?php

use App\Exceptions\BusinessRuleException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

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

    })
    ->create();