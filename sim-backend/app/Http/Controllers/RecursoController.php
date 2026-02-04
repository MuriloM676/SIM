<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecursoController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->get('usuario');
        $perPage = $request->get('per_page', 15);
        
        $query = DB::table('recursos as r')
            ->join('multas as m', 'r.multa_id', '=', 'm.id')
            ->join('usuarios as u', 'r.usuario_id', '=', 'u.id')
            ->select(
                'r.*',
                'm.auto_infracao',
                'm.placa',
                'u.nome as usuario_nome'
            );

        if ($usuario->perfil !== 'administrador') {
            $query->where('m.municipio_id', $usuario->municipio_id);
        }

        if ($request->has('status')) {
            $query->where('r.status', $request->status);
        }

        $total = $query->count();
        $recursos = $query->orderBy('r.created_at', 'desc')
            ->skip(($request->get('page', 1) - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recursos,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $request->get('page', 1),
                'last_page' => ceil($total / $perPage),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'multa_id' => 'required|exists:multas,id',
            'tipo' => 'required|in:defesa_previa,recurso_primeira_instancia,recurso_segunda_instancia',
            'argumentacao' => 'required|string|min:50',
            'documentos' => 'nullable|array',
        ]);

        $multa = DB::table('multas')->where('id', $request->multa_id)->first();
        $usuario = $request->get('usuario');

        if ($usuario->perfil !== 'administrador' && $multa->municipio_id != $usuario->municipio_id) {
            return response()->json(['message' => 'Sem permissão'], 403);
        }

        // Verificar se já existe recurso pendente
        $recursoExistente = DB::table('recursos')
            ->where('multa_id', $request->multa_id)
            ->whereIn('status', ['pendente', 'em_analise'])
            ->exists();

        if ($recursoExistente) {
            return response()->json(['message' => 'Já existe um recurso pendente para esta multa'], 422);
        }

        $recursoId = DB::table('recursos')->insertGetId([
            'multa_id' => $request->multa_id,
            'usuario_id' => $usuario->id,
            'tipo' => $request->tipo,
            'status' => 'pendente',
            'argumentacao' => $request->argumentacao,
            'data_protocolo' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Atualizar status da multa
        DB::table('multas')->where('id', $request->multa_id)->update([
            'status' => 'em_recurso',
            'updated_at' => now(),
        ]);

        // Auditar
        DB::table('auditorias')->insert([
            'usuario_id' => $usuario->id,
            'municipio_id' => $multa->municipio_id,
            'tipo' => 'criacao',
            'entidade' => 'Recurso',
            'entidade_id' => $recursoId,
            'descricao' => 'Abertura de recurso: ' . $request->tipo,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data_hora' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => ['id' => $recursoId, 'protocolo' => str_pad($recursoId, 8, '0', STR_PAD_LEFT)],
            'message' => 'Recurso aberto com sucesso'
        ], 201);
    }

    public function show($id)
    {
        $recurso = DB::table('recursos as r')
            ->join('multas as m', 'r.multa_id', '=', 'm.id')
            ->join('usuarios as u', 'r.usuario_id', '=', 'u.id')
            ->leftJoin('usuarios as ua', 'r.usuario_analise_id', '=', 'ua.id')
            ->select(
                'r.*',
                'm.auto_infracao',
                'm.placa',
                'm.data_infracao',
                'm.local_infracao',
                'u.nome as usuario_nome',
                'ua.nome as analista_nome'
            )
            ->where('r.id', $id)
            ->first();

        if (!$recurso) {
            return response()->json(['message' => 'Recurso não encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => $recurso]);
    }

    public function analisar(Request $request, $id)
    {
        $request->validate([
            'decisao' => 'required|in:deferido,indeferido,parcialmente_deferido',
            'parecer' => 'required|string|min:50',
        ]);

        $recurso = DB::table('recursos')->where('id', $id)->first();
        if (!$recurso) {
            return response()->json(['message' => 'Recurso não encontrado'], 404);
        }

        $multa = DB::table('multas')->where('id', $recurso->multa_id)->first();
        $usuario = $request->get('usuario');

        if ($usuario->perfil === 'operador') {
            return response()->json(['message' => 'Apenas gestores podem analisar recursos'], 403);
        }

        if ($usuario->perfil !== 'administrador' && $multa->municipio_id != $usuario->municipio_id) {
            return response()->json(['message' => 'Sem permissão'], 403);
        }

        DB::table('recursos')->where('id', $id)->update([
            'status' => 'analisado',
            'decisao' => $request->decisao,
            'parecer' => $request->parecer,
            'usuario_analise_id' => $usuario->id,
            'data_analise' => now(),
            'updated_at' => now(),
        ]);

        // Atualizar multa conforme decisão
        $novoStatus = $request->decisao === 'deferido' ? 'cancelada' : 'notificada';
        DB::table('multas')->where('id', $recurso->multa_id)->update([
            'status' => $novoStatus,
            'updated_at' => now(),
        ]);

        // Auditar
        DB::table('auditorias')->insert([
            'usuario_id' => $usuario->id,
            'municipio_id' => $multa->municipio_id,
            'tipo' => 'analise',
            'entidade' => 'Recurso',
            'entidade_id' => $id,
            'descricao' => 'Análise de recurso: ' . $request->decisao,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data_hora' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Recurso analisado com sucesso']);
    }
}
