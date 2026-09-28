<?php

namespace App\Policies;

use App\Models\Recordatorio;
use App\Models\Usuario;

class RecordatorioPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return in_array(
            $usuario->rol,
            ['estudiante', 'profesor', 'admin'],
            true
        );
    }

    public function view(
        Usuario $usuario,
        Recordatorio $recordatorio
    ): bool {
        return $usuario->esAdmin()
            || $recordatorio->usuario_id === $usuario->id;
    }

    public function create(Usuario $usuario): bool
    {
        return in_array(
            $usuario->rol,
            ['estudiante', 'profesor', 'admin'],
            true
        );
    }

    public function update(
        Usuario $usuario,
        Recordatorio $recordatorio
    ): bool {
        return $usuario->esAdmin()
            || $recordatorio->usuario_id === $usuario->id;
    }

    public function delete(
        Usuario $usuario,
        Recordatorio $recordatorio
    ): bool {
        return $usuario->esAdmin()
            || $recordatorio->usuario_id === $usuario->id;
    }

    public function restore(
        Usuario $usuario,
        Recordatorio $recordatorio
    ): bool {
        return false;
    }

    public function forceDelete(
        Usuario $usuario,
        Recordatorio $recordatorio
    ): bool {
        return false;
    }
}