<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'nombre1',
        'nombre2',
        'apellido1',
        'apellido2',
        'correo',
        'telefono',
        'contrasena',
        'rol',
    ];

    protected $hidden = [
        'contrasena',
    ];

    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed',
        ];
    }

    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function esProfesor(): bool
    {
        return $this->rol === 'profesor';
    }

    public function esEstudiante(): bool
    {
        return $this->rol === 'estudiante';
    }

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