<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePendienteRequest;
use App\Http\Requests\UpdatePendienteRequest;
use App\Http\Requests\StorePendienteConRecordatorioRequest;
use App\Models\Pendiente;
use App\Services\PendienteService;
use Illuminate\Http\Request;

class PendienteController extends Controller
{
    public function __construct(
        private PendienteService $service
    ) {}

    public function index(Request $request)
    {
        return response()->json(
            $this->service->listar($request->all())
        );
    }

    public function store(StorePendienteRequest $request)
    {
        $pendiente = $this->service->crear(
            $request->validated()
        );

        return response()->json($pendiente, 201);
    }

    public function storeConRecordatorio(
        StorePendienteConRecordatorioRequest $request
    ) {
        $pendiente = $this->service->crearConRecordatorio(
            $request->validated()
        );

        return response()->json($pendiente, 201);
    }

    public function show(Pendiente $pendiente)
    {
        return response()->json($pendiente);
    }

    public function update(
        UpdatePendienteRequest $request,
        Pendiente $pendiente
    ) {
        $pendiente = $this->service->actualizar(
            $pendiente,
            $request->validated()
        );

        return response()->json($pendiente);
    }

    public function destroy(Pendiente $pendiente)
    {
        $this->service->eliminar($pendiente);

        return response()->json(null, 204);
    }
}