<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePendienteConRecordatorioRequest;
use App\Http\Requests\StorePendienteRequest;
use App\Http\Requests\UpdatePendienteRequest;
use App\Http\Resources\PendienteResource;
use App\Http\Resources\ResumenEstadoResource;
use App\Models\Pendiente;
use App\Services\PendienteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Dedoc\Scramble\Attributes\Response;

class PendienteController extends Controller
{
    public function __construct(
        private PendienteService $service
    ) {}

    /**
     * Listar pendientes
     *
     * Obtiene una lista paginada de pendientes.
     * Permite aplicar filtros por estado, curso y próximos,
     * además de configurar el orden de los resultados.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Pendiente::class);

        $pendientes = $this->service->listar(
            $request->user(),
            $request->all()
        );

        return PendienteResource::collection($pendientes);
    }

    /**
     * Crear pendiente
     *
     * Crea un nuevo pendiente con los datos proporcionados.
     */
    #[Response(
        409,
        'No se puede crear el pendiente porque incumple una regla de negocio.',
        type: 'array{message: string}'
    )]
    public function store(StorePendienteRequest $request)
    {
        Gate::authorize('create', Pendiente::class);

        $pendiente = $this->service->crear(
            $request->user(),
            $request->validated()
        );

        return (new PendienteResource($pendiente))
            ->response()
            ->setStatusCode(201)
            ->header(
                'Location',
                url("/api/pendientes/{$pendiente->id}")
            );
    }

    /**
     * Crear pendiente con recordatorio
     *
     * Crea un pendiente junto con su recordatorio asociado.
     */
    public function storeConRecordatorio(
        StorePendienteConRecordatorioRequest $request
    ) {
        Gate::authorize('create', Pendiente::class);

        $pendiente = $this->service->crearConRecordatorio(
            $request->user(),
            $request->validated()
        );

        return (new PendienteResource($pendiente))
            ->response()
            ->setStatusCode(201)
            ->header(
                'Location',
                url("/api/pendientes/{$pendiente->id}")
            );
    }

    /**
     * Mostrar pendiente
     *
     * Obtiene la información de un pendiente específico.
     */
    public function show(
        Request $request,
        Pendiente $pendiente
    ) {
        Gate::authorize('view', $pendiente);

        $pendiente = $this->service->mostrar(
            $request->user(),
            $pendiente
        );

        return new PendienteResource($pendiente);
    }

    /**
     * Actualizar pendiente
     *
     * Actualiza los datos de un pendiente existente.
     */
    #[Response(
        409,
        'No se puede actualizar el pendiente porque incumple una regla de negocio.',
        type: 'array{message: string}'
    )]
    public function update(
        UpdatePendienteRequest $request,
        Pendiente $pendiente
    ) {
        Gate::authorize('update', $pendiente);

        $pendiente = $this->service->actualizar(
            $request->user(),
            $pendiente,
            $request->validated()
        );

        return new PendienteResource($pendiente);
    }

    /**
     * Eliminar pendiente
     *
     * Elimina un pendiente existente.
     */
    public function destroy(
        Request $request,
        Pendiente $pendiente
    ) {
        Gate::authorize('delete', $pendiente);

        $this->service->eliminar(
            $request->user(),
            $pendiente
        );

        return response()->noContent();
    }

    /**
     * Resumen de pendientes por estado
     *
     * Obtiene la cantidad de pendientes agrupados por estado.
     */
    public function resumenEstados(Request $request)
    {
        Gate::authorize('viewAny', Pendiente::class);

        $resumen = $this->service->resumenPorEstado(
            $request->user()
        );

        return ResumenEstadoResource::collection($resumen);
    }
}