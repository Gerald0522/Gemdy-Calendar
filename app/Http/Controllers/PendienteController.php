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
        $pendientes = $this->service->listar($request->all());

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
        $pendiente = $this->service->crear($request->validated());

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
     *
     * Este método se conserva como parte de la lógica desarrollada
     * anteriormente, aunque no se expone actualmente mediante una ruta.
     */
    public function storeConRecordatorio(
        StorePendienteConRecordatorioRequest $request
    ) {
        $pendiente = $this->service->crearConRecordatorio(
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
    public function show(Pendiente $pendiente)
    {
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
        $pendiente = $this->service->actualizar(
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
    public function destroy(Pendiente $pendiente)
    {
        $this->service->eliminar($pendiente);

        return response()->noContent();
    }

    /**
     * Resumen de pendientes por estado
     *
     * Obtiene la cantidad de pendientes agrupados por estado.
     */
    public function resumenEstados()
    {
        $resumen = $this->service->resumenPorEstado();

        return ResumenEstadoResource::collection($resumen);
    }
}