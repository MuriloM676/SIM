<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MultaController extends Controller
{
    /**
     * Lista multas com filtros e paginação
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DB::table('multas')
                ->select(
                    'multas.*',
                    'infracoes.codigo_ctb',
                    'infracoes.descricao as infracao_descricao',
                    'infracoes.valor as valor_multa',
                    'veiculos.placa',
                    'agentes.nome as agente_nome'
                )
                ->leftJoin('infracoes', 'multas.infracao_id', '=', 'infracoes.id')
                ->leftJoin('veiculos', 'multas.veiculo_id', '=', 'veiculos.id')
                ->leftJoin('agentes', 'multas.agente_id', '=', 'agentes.id');

            // Filtro por município (não-admin)
            if ($request->user_perfil !== 'administrador') {
                $query->where('multas.municipio_id', $request->user_municipio_id);
            }

            // Filtros opcionais
            if ($request->filled('status')) {
                $query->where('multas.status', $request->status);
            }

            if ($request->filled('data_inicio')) {
                $query->where('multas.data_infracao', '>=', $request->data_inicio);
            }

            if ($request->filled('data_fim')) {
                $query->where('multas.data_infracao', '<=', $request->data_fim);
            }

            if ($request->filled('placa')) {
                $query->where('veiculos.placa', 'LIKE', '%' . $request->placa . '%');
            }

            // Paginação
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);
            
            $total = $query->count();
            $multas = $query
                ->orderBy('multas.created_at', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $multas,
                'meta' => [
                    'current_page' => (int)$page,
                    'last_page' => (int)ceil($total / $perPage),
                    'per_page' => (int)$perPage,
                    'total' => $total,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar multas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cria nova multa
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'veiculo_id' => 'required|exists:veiculos,id',
            'agente_id' => 'required|exists:agentes,id',
            'infracao_id' => 'required|exists:infracoes,id',
            'placa' => 'required|string|max:7',
            'data_infracao' => 'required|date',
            'hora_infracao' => 'required',
            'local_infracao' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'velocidade_medida' => 'nullable|numeric',
            'velocidade_maxima' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Buscar valor da infração
            $infracao = DB::table('infracoes')->where('id', $request->infracao_id)->first();
            
            if (!$infracao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Infração não encontrada',
                ], 404);
            }

            // Gerar auto de infração
            $ano = date('Y');
            $ultimoAuto = DB::table('multas')
                ->where('auto_infracao', 'LIKE', $ano . '%')
                ->orderBy('auto_infracao', 'desc')
                ->value('auto_infracao');
            
            $proximoNumero = $ultimoAuto ? (int)substr($ultimoAuto, -6) + 1 : 1;
            $autoInfracao = $ano . str_pad($proximoNumero, 6, '0', STR_PAD_LEFT);

            $multaId = DB::table('multas')->insertGetId([
                'municipio_id' => $request->user_municipio_id,
                'veiculo_id' => $request->veiculo_id,
                'agente_id' => $request->agente_id,
                'infracao_id' => $request->infracao_id,
                'usuario_criador_id' => $request->user_id,
                'auto_infracao' => $autoInfracao,
                'placa' => strtoupper($request->placa),
                'data_infracao' => $request->data_infracao,
                'hora_infracao' => $request->hora_infracao,
                'local_infracao' => $request->local_infracao,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'velocidade_medida' => $request->velocidade_medida,
                'velocidade_maxima' => $request->velocidade_maxima,
                'observacoes' => $request->observacoes,
                'valor_multa' => $infracao->valor,
                'pontos_cnh' => $infracao->pontos,
                'status' => 'rascunho',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Auditoria
            DB::table('auditorias')->insert([
                'usuario_id' => $request->user_id,
                'municipio_id' => $request->user_municipio_id,
                'tipo' => 'criacao',
                'entidade' => 'Multa',
                'entidade_id' => $multaId,
                'descricao' => 'Criação de multa',
                'dados_depois' => json_encode(['status' => 'rascunho']),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'data_hora' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $multa = DB::table('multas')->where('id', $multaId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Multa criada com sucesso',
                'data' => $multa,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar multa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exibe uma multa específica
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $multa = DB::table('multas')
                ->select(
                    'multas.*',
                    'municipios.nome as municipio_nome',
                    'infracoes.codigo_ctb',
                    'infracoes.descricao as infracao_descricao',
                    'infracoes.gravidade',
                    'infracoes.valor',
                    'infracoes.pontos',
                    'veiculos.placa',
                    'veiculos.marca',
                    'veiculos.modelo',
                    'veiculos.proprietario_nome',
                    'agentes.nome as agente_nome',
                    'agentes.matricula as agente_matricula',
                    'usuarios.nome as criador_nome'
                )
                ->leftJoin('municipios', 'multas.municipio_id', '=', 'municipios.id')
                ->leftJoin('infracoes', 'multas.infracao_id', '=', 'infracoes.id')
                ->leftJoin('veiculos', 'multas.veiculo_id', '=', 'veiculos.id')
                ->leftJoin('agentes', 'multas.agente_id', '=', 'agentes.id')
                ->leftJoin('usuarios', 'multas.usuario_criador_id', '=', 'usuarios.id')
                ->where('multas.id', $id)
                ->first();

            if (!$multa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Multa não encontrada',
                ], 404);
            }

            // Verificação de autorização
            if ($request->user_perfil !== 'administrador' && $multa->municipio_id != $request->user_municipio_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não autorizado',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $multa,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar multa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Atualiza uma multa
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'local_infracao' => 'sometimes|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'velocidade_medida' => 'nullable|numeric',
            'velocidade_maxima' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $multa = DB::table('multas')->where('id', $id)->first();

            if (!$multa) {
                return response()->json(['success' => false, 'message' => 'Multa não encontrada'], 404);
            }

            // Só pode editar rascunho ou registrada
            if (!in_array($multa->status, ['rascunho', 'registrada'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Multa no status ' . $multa->status . ' não pode ser editada',
                ], 422);
            }

            DB::table('multas')->where('id', $id)->update(array_merge(
                $request->only(['local_infracao', 'latitude', 'longitude', 'velocidade_medida', 'velocidade_maxima', 'observacoes']),
                ['updated_at' => now()]
            ));

            // Auditoria
            DB::table('auditorias')->insert([
                'usuario_id' => $request->user_id,
                'municipio_id' => $request->user_municipio_id,
                'tipo' => 'edicao',
                'entidade' => 'Multa',
                'entidade_id' => $id,
                'descricao' => 'Edição de multa',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'data_hora' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Multa atualizada com sucesso',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar multa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Muda o status da multa
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string',
            'justificativa' => 'nullable|string|max:500',
        ]);

        try {
            $multa = DB::table('multas')->where('id', $id)->first();

            if (!$multa) {
                return response()->json(['success' => false, 'message' => 'Multa não encontrada'], 404);
            }

            $statusAntigo = $multa->status;
            
            DB::table('multas')->where('id', $id)->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);

            // Auditoria
            DB::table('auditorias')->insert([
                'usuario_id' => $request->user_id,
                'municipio_id' => $request->user_municipio_id,
                'tipo' => 'mudanca_status',
                'entidade' => 'Multa',
                'entidade_id' => $id,
                'descricao' => "Mudança de status: {$statusAntigo} → {$request->status}",
                'dados_antes' => json_encode(['status' => $statusAntigo]),
                'dados_depois' => json_encode(['status' => $request->status, 'justificativa' => $request->justificativa]),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'data_hora' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status atualizado com sucesso',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancela uma multa
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'justificativa' => 'required|string|min:10|max:500',
        ]);

        try {
            DB::table('multas')->where('id', $id)->update([
                'status' => 'cancelada',
                'updated_at' => now(),
            ]);

            // Auditoria
            DB::table('auditorias')->insert([
                'usuario_id' => $request->user_id,
                'municipio_id' => $request->user_municipio_id,
                'tipo' => 'cancelamento',
                'entidade' => 'Multa',
                'entidade_id' => $id,
                'descricao' => 'Cancelamento de multa',
                'dados_depois' => json_encode(['justificativa' => $request->justificativa]),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'data_hora' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Multa cancelada com sucesso',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar multa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Envia multa para o Detran
     */
    public function sendToDetran(Request $request, int $id): JsonResponse
    {
        try {
            DB::table('multas')->where('id', $id)->update([
                'status' => 'enviada_orgao_externo',
                'updated_at' => now(),
            ]);

            // Despachar job (quando configurado)
            // EnviarMultaDetran::dispatch($id);

            return response()->json([
                'success' => true,
                'message' => 'Multa enviada para processamento no Detran',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar multa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Estatísticas para dashboard
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $municipioId = $request->user_perfil === 'administrador' ? null : $request->user_municipio_id;

            $query = DB::table('multas');
            if ($municipioId) {
                $query->where('municipio_id', $municipioId);
            }

            $totalMultas = (clone $query)->count();
            $multasMes = (clone $query)->whereMonth('created_at', date('m'))->count();
            $multasHoje = (clone $query)->whereDate('created_at', today())->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_multas' => $totalMultas,
                    'multas_mes' => $multasMes,
                    'multas_hoje' => $multasHoje,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas: ' . $e->getMessage(),
            ], 500);
        }
    }
}
