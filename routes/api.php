<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PendienteController;
use App\Http\Controllers\CursoController;
use App\Models\Pendiente;


Route::get('/pendientes/proximos', function () {
    return Pendiente::proximos()->get();
});

Route::get('/pendientes/resumen/estado', function () {
    return Pendiente::selectRaw(
        'estado, COUNT(*) as cantidad'
    )
        ->groupBy('estado')
        ->get();
});


Route::post(
    '/pendientes/con-recordatorio',
    [PendienteController::class, 'storeConRecordatorio']
);


Route::apiResource(
    'pendientes',
    PendienteController::class
);

Route::apiResource(
    'cursos',
    CursoController::class
);