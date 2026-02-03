<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $municipioId = $request->user_municipio_id ?? 1;
        $mesAtual = date('Y-m');
        $anoAtual = date('Y');

        // Total de multas
        $totalMultas = DB::table('multas')
            ->where('municipio_id', $municipioId)
            ->whereNull('deleted_at')
            ->count();

        // Multas por status
        $multasPorStatus = DB::table('multas')
            ->select('status', DB::raw('count(*) as total'))
            ->where('municipio_id', $municipioId)
            ->whereNull('deleted_at')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Multas do mês
        $multasMes = DB::table('multas')
            ->where('municipio_id', $municipioId)
            ->whereNull('deleted_at')
            ->whereRaw("TO_CHAR(created_at, 'YYYY-MM') = ?", [$mesAtual])
            ->count();

        // Arrecadação estimada
        $arrecadacaoEstimada = DB::table('multas')
            ->where('municipio_id', $municipioId)
            ->whereIn('status', ['notificada', 'encerrada'])
            ->whereNull('deleted_at')
            ->sum('valor_multa');

        // Recursos pendentes
        $recursosPendentes = DB::table('recursos')
            ->join('multas', 'recursos.multa_id', '=', 'multas.id')
            ->where('multas.municipio_id', $municipioId)
            ->where('recursos.status', 'em_analise')
            ->count();

        // Evolução mensal (últimos 6 meses)
        $evolucaoMensal = DB::table('multas')
            ->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mes"),
                DB::raw('count(*) as total')
            )
            ->where('municipio_id', $municipioId)
            ->whereNull('deleted_at')
            ->whereRaw("created_at >= NOW() - INTERVAL '6 months'")
            ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM')"))
            ->orderBy('mes')
            ->get();

        // Top 5 infrações
        $topInfracoes = DB::table('multas')
            ->join('infracoes', 'multas.infracao_id', '=', 'infracoes.id')
            ->select(
                'infracoes.codigo_ctb',
                'infracoes.descricao',
                DB::raw('count(*) as total')
            )
            ->where('multas.municipio_id', $municipioId)
            ->whereNull('multas.deleted_at')
            ->groupBy('infracoes.id', 'infracoes.codigo_ctb', 'infracoes.descricao')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Atividades recentes
        $atividadesRecentes = DB::table('auditorias')
            ->join('usuarios', 'auditorias.usuario_id', '=', 'usuarios.id')
            ->select(
                'auditorias.*',
                'usuarios.nome as usuario_nome'
            )
            ->where('auditorias.municipio_id', $municipioId)
            ->orderBy('auditorias.created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'totais' => [
                    'total_multas' => $totalMultas,
                    'multas_mes' => $multasMes,
                    'arrecadacao_estimada' => $arrecadacaoEstimada,
                    'recursos_pendentes' => $recursosPendentes,
                ],
                'multas_por_status' => $multasPorStatus,
                'evolucao_mensal' => $evolucaoMensal,
                'top_infracoes' => $topInfracoes,
                'atividades_recentes' => $atividadesRecentes,
            ],
        ]);
    }

    public function relatorio(Request $request)
    {
        $municipioId = $request->user_municipio_id ?? 1;
        $tipo = $request->input('tipo', 'multas');
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');

        if ($tipo === 'multas') {
            $query = DB::table('multas')
                ->join('veiculos', 'multas.veiculo_id', '=', 'veiculos.id')
                ->join('infracoes', 'multas.infracao_id', '=', 'infracoes.id')
                ->join('agentes', 'multas.agente_id', '=', 'agentes.id')
                ->select(
                    'multas.auto_infracao',
                    'multas.status',
                    'multas.data_infracao',
                    'multas.local_infracao',
                    'multas.valor_multa',
                    'veiculos.placa',
                    'infracoes.codigo_ctb',
                    'infracoes.descricao as infracao',
                    'agentes.nome as agente'
                )
                ->where('multas.municipio_id', $municipioId)
                ->whereNull('multas.deleted_at');

            if ($dataInicio) {
                $query->where('multas.data_infracao', '>=', $dataInicio);
            }

            if ($dataFim) {
                $query->where('multas.data_infracao', '<=', $dataFim);
            }

            $dados = $query->orderBy('multas.data_infracao', 'desc')->get();

        } elseif ($tipo === 'recursos') {
            $query = DB::table('recursos')
                ->join('multas', 'recursos.multa_id', '=', 'multas.id')
                ->select(
                    'recursos.numero_protocolo',
                    'recursos.tipo',
                    'recursos.status',
                    'recursos.decisao',
                    'recursos.data_protocolo',
                    'recursos.data_julgamento',
                    'multas.auto_infracao'
                )
                ->where('multas.municipio_id', $municipioId);

            if ($dataInicio) {
                $query->where('recursos.data_protocolo', '>=', $dataInicio);
            }

            if ($dataFim) {
                $query->where('recursos.data_protocolo', '<=', $dataFim);
            }

            $dados = $query->orderBy('recursos.data_protocolo', 'desc')->get();

        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de relatório inválido',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $dados,
        ]);
    }
}
