<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Estatístico</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .section { margin-bottom: 25px; }
        .section-title { font-weight: bold; font-size: 14px; background-color: #f0f0f0; padding: 8px; margin-bottom: 10px; }
        .stats-grid { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; }
        .stat-box { flex: 1; min-width: 150px; border: 1px solid #ddd; padding: 15px; text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #2563eb; }
        .stat-label { font-size: 11px; color: #666; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 11px; }
        table th { background-color: #f0f0f0; font-weight: bold; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RELATÓRIO ESTATÍSTICO DE MULTAS</h1>
        <p>Período: {{ date('d/m/Y', strtotime($dataInicio)) }} a {{ date('d/m/Y', strtotime($dataFim)) }}</p>
    </div>

    <div class="section">
        <div class="section-title">RESUMO GERAL</div>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-value">{{ number_format($totalMultas, 0, ',', '.') }}</div>
                <div class="stat-label">Total de Multas</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">R$ {{ number_format($arrecadacao, 2, ',', '.') }}</div>
                <div class="stat-label">Arrecadação Total</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">R$ {{ number_format($totalMultas > 0 ? $arrecadacao / $totalMultas : 0, 2, ',', '.') }}</div>
                <div class="stat-label">Valor Médio</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">MULTAS POR STATUS</div>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th style="text-align: center;">Quantidade</th>
                    <th style="text-align: center;">Percentual</th>
                </tr>
            </thead>
            <tbody>
                @foreach($porStatus as $status)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $status->status)) }}</td>
                    <td style="text-align: center;">{{ $status->total }}</td>
                    <td style="text-align: center;">{{ number_format(($status->total / $totalMultas) * 100, 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">TOP 10 INFRAÇÕES</div>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th style="text-align: center;">Quantidade</th>
                    <th style="text-align: right;">Valor Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topInfracoes as $infracao)
                <tr>
                    <td>{{ $infracao->codigo_ctb }}</td>
                    <td>{{ $infracao->descricao }}</td>
                    <td style="text-align: center;">{{ $infracao->total }}</td>
                    <td style="text-align: right;">R$ {{ number_format($infracao->valor_total, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Relatório gerado em {{ date('d/m/Y H:i:s') }}</p>
        <p>Sistema Integrado de Multas (SIM) - Documento Oficial</p>
    </div>
</body>
</html>
