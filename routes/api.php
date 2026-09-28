<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PendienteController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\RecordatorioController;
use App\Http\Controllers\AuthController;


// Autenticación
Route::prefix('auth')->group(function () {

    // Rutas públicas

    Route::post(
        '/register',
        [AuthController::class, 'register']
    );

    Route::post(
        '/login',
        [AuthController::class, 'login']
    )->middleware('throttle:5,1');


    // Rutas que requieren autenticación

    Route::middleware('auth:sanctum')->group(function () {

        Route::get(
            '/me',
            [AuthController::class, 'me']
        );

        Route::post(
            '/logout',
            [AuthController::class, 'logout']
        );
    });
});

// Rutas protegidas de la aplicación
Route::middleware('auth:sanctum')->group(function () {

    
    // Pendientes
    Route::get(
        '/pendientes-estados',
        [PendienteController::class, 'resumenEstados']
    );

    Route::post(
        '/pendientes/con-recordatorio',
        [PendienteController::class, 'storeConRecordatorio']
    );



    //Recordatorios


    Route::get(
        '/pendientes/{pendiente}/recordatorios',
        [RecordatorioController::class, 'index']
    );

    Route::get(
        '/pendientes/{pendiente}/recordatorios/{recordatorio}',
        [RecordatorioController::class, 'show']
    );

    Route::post(
        '/pendientes/{pendiente}/recordatorios',
        [RecordatorioController::class, 'store']
    );


    //Pendientes - CRUD


    Route::apiResource(
        'pendientes',
        PendienteController::class
    );


    //Cursos


    // Estudiante, profesor y admin pueden listar cursos.
    Route::get(
        '/cursos',
        [CursoController::class, 'index']
    )->middleware(
        'role:estudiante,profesor,admin'
    )->name('cursos.index');


    // Estudiante, profesor y admin pueden crear cursos.
    Route::post(
        '/cursos',
        [CursoController::class, 'store']
    )->middleware(
        'role:estudiante,profesor,admin'
    )->name('cursos.store');


    // Los tres roles pueden consultar cursos.
    // La Policy controla si el recurso pertenece al usuario.
    Route::get(
        '/cursos/{curso}',
        [CursoController::class, 'show']
    )->middleware(
        'role:estudiante,profesor,admin'
    )->name('cursos.show');


    // Los tres roles pueden actualizar.
    // La Policy controla la propiedad del curso.
    Route::match(
        ['put', 'patch'],
        '/cursos/{curso}',
        [CursoController::class, 'update']
    )->middleware(
        'role:estudiante,profesor,admin'
    )->name('cursos.update');


    // Solo profesor y admin pueden eliminar cursos.
    Route::delete(
        '/cursos/{curso}',
        [CursoController::class, 'destroy']
    )->middleware(
        'role:profesor,admin'
    )->name('cursos.destroy');
});