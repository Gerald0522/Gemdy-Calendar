<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etiqueta extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'color',
    ];

    public function pendientes()
    {
        return $this->belongsToMany(
            Pendiente::class,
            'pendiente_etiqueta'
        )
        ->withPivot([
            'prioridad',
            'fecha_asignacion'
        ])
        ->withTimestamps();
    }
}
