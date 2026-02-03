<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function __construct(
        private AuditoriaService $auditoriaService
    ) {}

    /**
     * Lista logs de auditoria
     */
    public function index(Request $request): JsonResponse
    {
        // Apenas administradores e auditores podem ver logs
        if (!$request->user()->temPermissao('auditoria.visualizar')) {
            return response()->json([
                'success' => false,
                'message' => 'Sem permissão para acessar auditoria',
            ], 403);
        }

        $filtros = $request->only([
            'usuario_id',
            'entidade',
            'entidade_id',
            'tipo_acao',
            'data_inicio',
            'data_fim',
            'criticas',
        ]);

        $perPage = $request->input('per_page', 50);

        $logs = $this->auditoriaService->buscarLogs($filtros, $perPage);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * Exibe log específico
     */
    public function show(int $id): JsonResponse
    {
        $log = \App\Models\Auditoria::with('usuario')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }
}
