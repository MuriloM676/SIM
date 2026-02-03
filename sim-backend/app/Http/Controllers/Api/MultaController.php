<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Multa\CreateMultaDTO;
use App\DTOs\Multa\MultaFilterDTO;
use App\DTOs\Multa\UpdateMultaDTO;
use App\Enums\MultaStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Multa\StoreMultaRequest;
use App\Http\Requests\Multa\UpdateMultaRequest;
use App\Services\MultaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MultaController extends Controller
{
    public function __construct(
        private MultaService $multaService
    ) {}

    /**
     * Lista multas com filtros
     */
    public function index(Request $request): JsonResponse
    {
        $filters = MultaFilterDTO::fromRequest($request->all());

        // Se não for admin, filtra pelo município do usuário
        if (!$request->user()->isAdministrador()) {
            $filters = MultaFilterDTO::fromRequest(
                array_merge($request->all(), ['municipio_id' => $request->user()->municipio_id])
            );
        }

        $multas = $this->multaService->listar($filters);

        return response()->json([
            'success' => true,
            'data' => $multas->items(),
            'meta' => [
                'current_page' => $multas->currentPage(),
                'last_page' => $multas->lastPage(),
                'per_page' => $multas->perPage(),
                'total' => $multas->total(),
            ],
        ]);
    }

    /**
     * Cria nova multa
     */
    public function store(StoreMultaRequest $request): JsonResponse
    {
        $dto = CreateMultaDTO::fromArray($request->validated());

        $multa = $this->multaService->criar($dto);

        return response()->json([
            'success' => true,
            'message' => 'Multa criada com sucesso',
            'data' => $multa->load(['infracao', 'veiculo', 'agente']),
        ], 201);
    }

    /**
     * Exibe uma multa específica
     */
    public function show(int $id): JsonResponse
    {
        $multa = $this->multaService->buscarPorId($id);

        // Autorização: usuário deve ser do mesmo município ou admin
        $this->authorize('view', $multa);

        return response()->json([
            'success' => true,
            'data' => $multa->load([
                'municipio',
                'infracao',
                'veiculo',
                'agente',
                'usuarioCriador',
                'historico.usuario',
                'evidencias',
                'recursos'
            ]),
        ]);
    }

    /**
     * Atualiza uma multa
     */
    public function update(UpdateMultaRequest $request, int $id): JsonResponse
    {
        $dto = UpdateMultaDTO::fromArray($request->validated());

        $multa = $this->multaService->atualizar($id, $dto);

        return response()->json([
            'success' => true,
            'message' => 'Multa atualizada com sucesso',
            'data' => $multa,
        ]);
    }

    /**
     * Muda o status da multa
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string'],
            'justificativa' => ['nullable', 'string', 'max:500'],
        ]);

        $novoStatus = MultaStatus::from($request->status);
        $justificativa = $request->justificativa;

        $multa = $this->multaService->mudarStatus($id, $novoStatus, $justificativa);

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado com sucesso',
            'data' => $multa,
        ]);
    }

    /**
     * Cancela uma multa
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'justificativa' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $multa = $this->multaService->cancelar($id, $request->justificativa);

        return response()->json([
            'success' => true,
            'message' => 'Multa cancelada com sucesso',
            'data' => $multa,
        ]);
    }

    /**
     * Envia multa para o Detran
     */
    public function sendToDetran(int $id): JsonResponse
    {
        $multa = $this->multaService->enviarParaDetran($id);

        return response()->json([
            'success' => true,
            'message' => 'Multa enviada para processamento no Detran',
            'data' => $multa,
        ]);
    }

    /**
     * Estatísticas para dashboard
     */
    public function statistics(Request $request): JsonResponse
    {
        $municipioId = $request->user()->municipio_id;

        $stats = $this->multaService->estatisticasPorMunicipio($municipioId);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
