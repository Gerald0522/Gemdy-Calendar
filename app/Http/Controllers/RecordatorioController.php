<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecordatorioRequest;
use App\Http\Resources\RecordatorioResource;
use App\Models\Pendiente;
use App\Models\Recordatorio;
use App\Services\RecordatorioService;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RecordatorioController extends Controller
{
    public function __construct(
        private RecordatorioService $service
    ) {}

    /**
     * Listar recordatorios de un pendiente
     *
     * Obtiene todos los recordatorios asociados
     * a un pendiente específico.
     */
    public function index(
        Request $request,
        Pendiente $pendiente
    ) {
        Gate::authorize('view', $pendiente);
        Gate::authorize('viewAny', Recordatorio::class);

        $recordatorios = $this->service->listarPorPendiente(
            $request->user(),
            $pendiente,
            $request->all()
        );

        return RecordatorioResource::collection($recordatorios);
    }

    /**
     * Mostrar recordatorio
     *
     * Obtiene un recordatorio específico
     * perteneciente a un pendiente.
     */
    public function show(
        Request $request,
        Pendiente $pendiente,
        Recordatorio $recordatorio
    ) {
        Gate::authorize('view', $pendiente);
        Gate::authorize('view', $recordatorio);

        $recordatorio = $this->service->mostrar(
            $request->user(),
            $pendiente,
            $recordatorio
        );

        return new RecordatorioResource($recordatorio);
    }

    /**
     * Crear recordatorio
     *
     * Crea un nuevo recordatorio asociado
     * a un pendiente específico.
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
        Gate::authorize('view', $pendiente);
        Gate::authorize('create', Recordatorio::class);

        $recordatorio = $this->service->crear(
            $request->user(),
            $pendiente,
            $request->validated()
        );

        return (new RecordatorioResource($recordatorio))
            ->response()
            ->setStatusCode(201)
            ->header(
                'Location',
                url(
                    "/api/pendientes/{$pendiente->id}/recordatorios/{$recordatorio->id}"
                )
            );
    }
}