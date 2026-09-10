<?php

use Illuminate\Support\Facades\Route;
use App\Models\Usuario;
use App\Models\Curso;
use App\Models\Horario;
use App\Models\Pendiente;
use App\Models\Etiqueta;
use App\Models\Recordatorio;

// Ver todos los datos

Route::get('/usuarios', function () {
    return Usuario::all();
});

Route::get('/cursos', function () {
    return Curso::all();
});

Route::get('/horarios', function () {
    return Horario::all();
});

Route::get('/pendientes', function () {
    return Pendiente::all();
});

Route::get('/etiquetas', function () {
    return Etiqueta::all();
});

Route::get('/recordatorios', function () {
    return Recordatorio::all();
});


Route::get('/pendientes/estado/pendiente', function () {
    return Pendiente::estado('pendiente')->get();
});

Route::get('/pendientes/proximos', function () {
    return Pendiente::proximos()->get();
});

Route::get('/pendientes/proximos-pendientes', function () {
    return Pendiente::estado('pendiente')
        ->proximos()
        ->get();
});

Route::get('/pendientes/curso/{cursoId}', function ($cursoId) {
    return Pendiente::delCurso($cursoId)->get();
});

Route::get('/pendientes/resumen/estado', function () {
    return Pendiente::selectRaw('estado, COUNT(*) as cantidad')
        ->groupBy('estado')
        ->get();
});
