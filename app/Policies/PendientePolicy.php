<?php

namespace App\Policies;

use App\Models\Pendiente;
use App\Models\Usuario;

class PendientePolicy
{

    //Listar pendientes
    public function viewAny(Usuario $usuario): bool
    {
        return in_array(
            $usuario->rol,
            ['estudiante', 'profesor', 'admin'],
            true
        );
    }

    //Ver un pendiente
    public function view(
        Usuario $usuario,
        Pendiente $pendiente
    ): bool {
        return $usuario->esAdmin()
            || $pendiente->usuario_id === $usuario->id;
    }

     //Crear pendiente
    public function create(Usuario $usuario): bool
    {
        return in_array(
            $usuario->rol,
            ['estudiante', 'profesor', 'admin'],
            true
        );
    }


    //Actualizar pendiente
    public function update(
        Usuario $usuario,
        Pendiente $pendiente
    ): bool {
        return $usuario->esAdmin()
            || $pendiente->usuario_id === $usuario->id;
    }

    //Eliminar pendiente
    public function delete(
        Usuario $usuario,
        Pendiente $pendiente
    ): bool {
        return $usuario->esAdmin()
            || $pendiente->usuario_id === $usuario->id;
    }


    public function restore(
        Usuario $usuario,
        Pendiente $pendiente
    ): bool {
        return false;
    }


    public function forceDelete(
        Usuario $usuario,
        Pendiente $pendiente
    ): bool {
        return false;
    }
}