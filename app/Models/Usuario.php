<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre1',
        'nombre2',
        'apellido1',
        'apellido2',
        'correo',
        'telefono',
        'contrasena',
    ];

    protected $hidden = [
        'contrasena',
    ];

    public function cursos()
    {
        return $this->hasMany(Curso::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function pendientes()
    {
        return $this->hasMany(Pendiente::class);
    }

    public function recordatorios()
    {
        return $this->hasMany(Recordatorio::class);
    }
}
