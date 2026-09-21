<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PendienteController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\RecordatorioController;



Route::get(
    '/pendientes-estados',
    [PendienteController::class, 'resumenEstados']
);



Route::get(
    '/pendientes/{pendiente}/recordatorios',
    [RecordatorioController::class, 'index']
);

Route::post(
    '/pendientes/{pendiente}/recordatorios',
    [RecordatorioController::class, 'store']
);

Route::apiResource(
    'pendientes',
    PendienteController::class
);

Route::apiResource(
    'cursos',
    CursoController::class
);