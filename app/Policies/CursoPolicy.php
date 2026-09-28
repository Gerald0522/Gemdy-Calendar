<?php

namespace App\Policies;

use App\Models\Curso;
use App\Models\Usuario;

class CursoPolicy
{
    /**
     * Permite consultar la lista de cursos.
     */
    public function viewAny(Usuario $usuario): bool
    {
        return in_array($usuario->rol, [
            'admin',
            'profesor',
            'estudiante',
        ]);
    }

    /**
     * Permite consultar un curso.
     *
     * El administrador puede consultar cualquier curso.
     * Los demás usuarios solamente sus propios cursos.
     */
    public function view(Usuario $usuario, Curso $curso): bool
    {
        return $usuario->esAdmin()
            || $curso->usuario_id === $usuario->id;
    }

    /**
     * Permite crear cursos.
     */
    public function create(Usuario $usuario): bool
    {
        return in_array($usuario->rol, [
            'admin',
            'profesor',
            'estudiante',
        ]);
    }

    /**
     * Permite actualizar un curso.
     *
     * El administrador puede modificar cualquier curso.
     * Los demás usuarios solamente sus propios cursos.
     */
    public function update(Usuario $usuario, Curso $curso): bool
    {
        return $usuario->esAdmin()
            || $curso->usuario_id === $usuario->id;
    }

    /**
     * Permite eliminar un curso.
     *
     * El administrador puede eliminar cualquier curso.
     * Los demás usuarios solamente sus propios cursos.
     */
    public function delete(Usuario $usuario, Curso $curso): bool
    {
        return $usuario->esAdmin()
            || $curso->usuario_id === $usuario->id;
    }

    public function restore(Usuario $usuario, Curso $curso): bool
    {
        return false;
    }

    public function forceDelete(Usuario $usuario, Curso $curso): bool
    {
        return false;
    }
}