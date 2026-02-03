<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RecursoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        
        $query = DB::table('recursos')
            ->join('multas', 'recursos.multa_id', '=', 'multas.id')
            ->join('usuarios', 'recursos.usuario_criador_id', '=', 'usuarios.id')
            ->select(
                'recursos.*',
                'multas.auto_infracao',
                'multas.valor_multa',
                'usuarios.nome as criador_nome'
            )
            ->where('multas.municipio_id', $request->user_municipio_id ?? 1)
            ->orderBy('recursos.created_at', 'desc');

        $total = $query->count();
        $recursos = $query->skip(($request->input('page', 1) - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recursos,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => (int)$request->input('page', 1),
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'multa_id' => 'required|exists:multas,id',
            'tipo' => 'required|in:defesa_previa,recurso_primeira_instancia,recurso_segunda_instancia',
            'fundamentacao' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verificar se multa pertence ao município
        $multa = DB::table('multas')
            ->where('id', $request->multa_id)
            ->where('municipio_id', $request->user_municipio_id ?? 1)
            ->first();

        if (!$multa) {
            return response()->json([
                'success' => false,
                'message' => 'Multa não encontrada',
            ], 404);
        }

        // Verificar se pode criar recurso
        if (!in_array($multa->status, ['notificada', 'em_recurso'])) {
            return response()->json([
                'success' => false,
                'message' => 'Multa deve estar notificada para criar recurso',
            ], 422);
        }

        $numeroProtocolo = 'REC-' . date('Y') . '-' . str_pad(
            DB::table('recursos')->count() + 1,
            6,
            '0',
            STR_PAD_LEFT
        );

        $recursoId = DB::table('recursos')->insertGetId([
            'numero_protocolo' => $numeroProtocolo,
            'multa_id' => $request->multa_id,
            'usuario_criador_id' => $request->user_id ?? 1,
            'tipo' => $request->tipo,
            'fundamentacao' => $request->fundamentacao,
            'status' => 'em_analise',
            'data_protocolo' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Atualizar status da multa
        DB::table('multas')->where('id', $request->multa_id)->update([
            'status' => 'em_recurso',
            'updated_at' => now(),
        ]);

        // Auditoria
        DB::table('auditorias')->insert([
            'usuario_id' => $request->user_id ?? 1,
            'municipio_id' => $request->user_municipio_id ?? 1,
            'tipo' => 'criacao',
            'entidade' => 'Recurso',
            'entidade_id' => $recursoId,
            'dados_depois' => json_encode(['protocolo' => $numeroProtocolo, 'tipo' => $request->tipo]),
            'descricao' => 'Recurso administrativo criado',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'base_legal_lgpd' => 'Art. 7º, II - Cumprimento de obrigação legal',
            'data_hora' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recurso criado com sucesso',
            'data' => ['id' => $recursoId, 'protocolo' => $numeroProtocolo],
        ], 201);
    }

    public function show($id, Request $request)
    {
        $recurso = DB::table('recursos')
            ->join('multas', 'recursos.multa_id', '=', 'multas.id')
            ->join('usuarios as criador', 'recursos.usuario_criador_id', '=', 'criador.id')
            ->leftJoin('usuarios as julgador', 'recursos.usuario_julgador_id', '=', 'julgador.id')
            ->select(
                'recursos.*',
                'multas.auto_infracao',
                'multas.valor_multa',
                'criador.nome as criador_nome',
                'julgador.nome as julgador_nome'
            )
            ->where('recursos.id', $id)
            ->where('multas.municipio_id', $request->user_municipio_id ?? 1)
            ->first();

        if (!$recurso) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso não encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $recurso,
        ]);
    }

    public function julgar($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'decisao' => 'required|in:deferido,indeferido',
            'parecer_tecnico' => 'nullable|string',
            'justificativa_decisao' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $recurso = DB::table('recursos')
            ->join('multas', 'recursos.multa_id', '=', 'multas.id')
            ->where('recursos.id', $id)
            ->where('multas.municipio_id', $request->user_municipio_id ?? 1)
            ->select('recursos.*', 'multas.id as multa_id')
            ->first();

        if (!$recurso) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso não encontrado',
            ], 404);
        }

        if ($recurso->status !== 'em_analise') {
            return response()->json([
                'success' => false,
                'message' => 'Recurso já foi julgado',
            ], 422);
        }

        DB::table('recursos')->where('id', $id)->update([
            'status' => 'analisado',
            'decisao' => $request->decisao,
            'parecer_tecnico' => $request->parecer_tecnico,
            'justificativa_decisao' => $request->justificativa_decisao,
            'usuario_julgador_id' => $request->user_id ?? 1,
            'data_julgamento' => now(),
            'updated_at' => now(),
        ]);

        // Atualizar multa conforme decisão
        $novoStatusMulta = $request->decisao === 'deferido' ? 'deferida' : 'indeferida';
        DB::table('multas')->where('id', $recurso->multa_id)->update([
            'status' => $novoStatusMulta,
            'updated_at' => now(),
        ]);

        // Auditoria
        DB::table('auditorias')->insert([
            'usuario_id' => $request->user_id ?? 1,
            'municipio_id' => $request->user_municipio_id ?? 1,
            'tipo' => 'alteracao',
            'entidade' => 'Recurso',
            'entidade_id' => $id,
            'dados_antes' => json_encode(['status' => 'em_analise']),
            'dados_depois' => json_encode(['status' => 'analisado', 'decisao' => $request->decisao]),
            'descricao' => "Recurso julgado: {$request->decisao}",
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'base_legal_lgpd' => 'Art. 7º, II - Cumprimento de obrigação legal',
            'data_hora' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recurso julgado com sucesso',
        ]);
    }
}
