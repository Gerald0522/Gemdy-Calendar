<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCursoRequest;
use App\Http\Requests\UpdateCursoRequest;
use App\Http\Resources\CursoResource;
use App\Models\Curso;
use App\Services\CursoService;
use Illuminate\Http\Request;
use Dedoc\Scramble\Attributes\Response;

class CursoController extends Controller
{
    public function __construct(
        private CursoService $service
    ) {}

    /**
     * Listar cursos
     *
     * Obtiene una lista paginada de cursos.
     * Permite aplicar filtros y configurar el orden de los resultados.
     */
    public function index(Request $request)
    {
        $cursos = $this->service->listar($request->all());

        return CursoResource::collection($cursos);
    }

    /**
     * Crear curso
     *
     * Crea un nuevo curso con los datos proporcionados.
     */
    public function store(StoreCursoRequest $request)
    {
        $curso = $this->service->crear($request->validated());

        return (new CursoResource($curso))
            ->response()
            ->setStatusCode(201)
            ->header(
                'Location',
                url("/api/cursos/{$curso->id}")
            );
    }

    /**
     * Mostrar curso
     *
     * Obtiene la información de un curso específico.
     */
    public function show(Curso $curso)
    {
        return new CursoResource($curso);
    }

    /**
     * Actualizar curso
     *
     * Actualiza los datos de un curso existente.
     */
    public function update(
        UpdateCursoRequest $request,
        Curso $curso
    ) {
        $curso = $this->service->actualizar(
            $curso,
            $request->validated()
        );

        return new CursoResource($curso);
    }

    /**
     * Eliminar curso
     *
     * Elimina un curso siempre que no tenga pendientes activos.
     */
    #[Response(
        409,
        'El curso tiene pendientes activos.',
        type: 'array{message: string}'
    )]
    public function destroy(Curso $curso)
    {
        $this->service->eliminar($curso);

        return response()->noContent();
    }
}