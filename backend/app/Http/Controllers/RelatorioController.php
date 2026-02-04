<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    public function multaPdf($id)
    {
        $multa = DB::table('multas as m')
            ->join('veiculos as v', 'm.veiculo_id', '=', 'v.id')
            ->join('infracoes as i', 'm.infracao_id', '=', 'i.id')
            ->join('agentes as a', 'm.agente_id', '=', 'a.id')
            ->join('municipios as mu', 'm.municipio_id', '=', 'mu.id')
            ->select(
                'm.*',
                'v.placa',
                'v.marca',
                'v.modelo',
                'v.cor',
                'v.proprietario_nome',
                'v.proprietario_cpf_cnpj',
                'i.codigo_ctb',
                'i.descricao as infracao_descricao',
                'i.gravidade',
                'i.pontos',
                'a.nome as agente_nome',
                'a.matricula as agente_matricula',
                'mu.nome as municipio_nome',
                'mu.uf as municipio_uf'
            )
            ->where('m.id', $id)
            ->first();

        if (!$multa) {
            return response()->json(['message' => 'Multa não encontrada'], 404);
        }

        $evidencias = DB::table('evidencias')
            ->where('multa_id', $id)
            ->whereNull('deleted_at')
            ->get();

        $pdf = PDF::loadView('pdf.multa', compact('multa', 'evidencias'));
        return $pdf->download('multa-' . $multa->auto_infracao . '.pdf');
    }

    public function estatisticasPdf(Request $request)
    {
        $usuario = $request->get('usuario');
        $dataInicio = $request->get('data_inicio', now()->startOfMonth());
        $dataFim = $request->get('data_fim', now()->endOfMonth());

        $query = DB::table('multas as m')
            ->join('infracoes as i', 'm.infracao_id', '=', 'i.id')
            ->whereBetween('m.data_infracao', [$dataInicio, $dataFim]);

        if ($usuario->perfil !== 'administrador') {
            $query->where('m.municipio_id', $usuario->municipio_id);
        }

        $totalMultas = $query->count();
        $arrecadacao = $query->sum('m.valor_multa');
        
        $porStatus = DB::table('multas')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->whereBetween('data_infracao', [$dataInicio, $dataFim])
            ->groupBy('status')
            ->get();

        $topInfracoes = DB::table('multas as m')
            ->join('infracoes as i', 'm.infracao_id', '=', 'i.id')
            ->select('i.codigo_ctb', 'i.descricao', DB::raw('COUNT(*) as total'), DB::raw('SUM(m.valor_multa) as valor_total'))
            ->whereBetween('m.data_infracao', [$dataInicio, $dataFim])
            ->groupBy('i.id', 'i.codigo_ctb', 'i.descricao')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $pdf = PDF::loadView('pdf.estatisticas', compact(
            'totalMultas',
            'arrecadacao',
            'porStatus',
            'topInfracoes',
            'dataInicio',
            'dataFim'
        ));

        return $pdf->download('relatorio-' . date('Y-m-d') . '.pdf');
    }

    public function dashboardCache(Request $request)
    {
        $usuario = $request->get('usuario');
        $cacheKey = 'dashboard_' . ($usuario->municipio_id ?? 'all');
        
        // Tentar obter do cache
        $cached = cache()->get($cacheKey);
        if ($cached) {
            return response()->json([
                'success' => true,
                'data' => $cached,
                'cached' => true
            ]);
        }

        // Se não estiver em cache, buscar do banco
        $queryBase = DB::table('multas as m');
        
        if ($usuario->perfil !== 'administrador' && $usuario->municipio_id) {
            $queryBase->where('m.municipio_id', $usuario->municipio_id);
        }

        $totalMultas = (clone $queryBase)->count();
        $multasMes = (clone $queryBase)
            ->whereYear('m.created_at', date('Y'))
            ->whereMonth('m.created_at', date('m'))
            ->count();

        $arrecadacao = (clone $queryBase)->sum('m.valor_multa') ?? 0;
        $recursosPendentes = DB::table('recursos')->where('status', 'pendente')->count();

        $multasPorStatus = (clone $queryBase)
            ->select('m.status', DB::raw('COUNT(*) as total'))
            ->groupBy('m.status')
            ->get()
            ->keyBy('status')
            ->map(fn($item) => ['status' => $item->status, 'total' => $item->total]);

        $evolucaoMensal = (clone $queryBase)
            ->select(
                DB::raw('TO_CHAR(m.data_infracao, \'YYYY-MM\') as mes'),
                DB::raw('COUNT(*) as total')
            )
            ->where('m.data_infracao', '>=', now()->subMonths(6))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $topInfracoes = DB::table('multas as m')
            ->join('infracoes as i', 'm.infracao_id', '=', 'i.id')
            ->select('i.descricao', DB::raw('COUNT(*) as total'))
            ->groupBy('i.id', 'i.descricao')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $atividadesRecentes = DB::table('auditorias as a')
            ->join('usuarios as u', 'a.usuario_id', '=', 'u.id')
            ->select('a.*', 'u.nome as usuario_nome')
            ->orderBy('a.data_hora', 'desc')
            ->limit(10)
            ->get();

        $data = [
            'totais' => [
                'total_multas' => $totalMultas,
                'multas_mes' => $multasMes,
                'arrecadacao_estimada' => number_format($arrecadacao, 2, '.', ''),
                'recursos_pendentes' => $recursosPendentes,
            ],
            'multas_por_status' => $multasPorStatus,
            'evolucao_mensal' => $evolucaoMensal,
            'top_infracoes' => $topInfracoes,
            'atividades_recentes' => $atividadesRecentes,
        ];

        // Cachear por 5 minutos
        cache()->put($cacheKey, $data, 300);

        return response()->json([
            'success' => true,
            'data' => $data,
            'cached' => false
        ]);
    }
}
