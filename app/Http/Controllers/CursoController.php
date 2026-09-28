<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCursoRequest;
use App\Http\Requests\UpdateCursoRequest;
use App\Http\Resources\CursoResource;
use App\Models\Curso;
use App\Services\CursoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        Gate::authorize('viewAny', Curso::class);

        $cursos = $this->service->listar(
            $request->user(),
            $request->all()
        );

        return CursoResource::collection($cursos);
    }

    /**
     * Crear curso
     *
     * Crea un nuevo curso con los datos proporcionados.
     */
    public function store(StoreCursoRequest $request)
    {
        Gate::authorize('create', Curso::class);

        $curso = $this->service->crear(
            $request->user(),
            $request->validated()
        );

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
    public function show(Request $request, Curso $curso)
    {
        Gate::authorize('view', $curso);

        $curso = $this->service->mostrar(
            $request->user(),
            $curso
        );

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
        Gate::authorize('update', $curso);

        $curso = $this->service->actualizar(
            $request->user(),
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
    public function destroy(Request $request, Curso $curso)
    {
        Gate::authorize('delete', $curso);

        $this->service->eliminar(
            $request->user(),
            $curso
        );

        return response()->noContent();
    }
}