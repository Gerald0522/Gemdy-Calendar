<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecordatorioRequest;
use App\Http\Resources\RecordatorioResource;
use App\Models\Pendiente;
use App\Services\RecordatorioService;
use Dedoc\Scramble\Attributes\Response;

class RecordatorioController extends Controller
{
    public function __construct(
        private RecordatorioService $service
    ) {}

    /**
     * Listar recordatorios de un pendiente
     *
     * Obtiene todos los recordatorios asociados a un pendiente específico.
     */
    public function index(Pendiente $pendiente)
    {
        $recordatorios = $this->service->listarPorPendiente($pendiente);

        return RecordatorioResource::collection($recordatorios);
    }

    /**
     * Crear recordatorio
     *
     * Crea un nuevo recordatorio asociado a un pendiente específico.
     */
    #[Response(
        409,
        'No se puede crear el recordatorio porque incumple una regla de negocio.',
        type: 'array{message: string}'
    )]
    public function store(
        StoreRecordatorioRequest $request,
        Pendiente $pendiente
    ) {
        $recordatorio = $this->service->crear(
            $pendiente,
            $request->validated()
        );

        return (new RecordatorioResource($recordatorio))
            ->response()
            ->setStatusCode(201)
            ->header(
                'Location',
                url("/api/pendientes/{$pendiente->id}/recordatorios")
            );
    }
}