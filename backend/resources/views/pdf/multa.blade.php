<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Auto de Infração - {{ $multa->auto_infracao }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; font-size: 14px; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px; }
        .info-row { margin: 5px 0; }
        .info-label { font-weight: bold; display: inline-block; width: 150px; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        table th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>AUTO DE INFRAÇÃO DE TRÂNSITO</h1>
        <p><strong>{{ $multa->municipio_nome }} - {{ $multa->municipio_uf }}</strong></p>
        <p>Nº {{ $multa->auto_infracao }}</p>
    </div>

    <div class="section">
        <div class="section-title">DADOS DA INFRAÇÃO</div>
        <div class="info-row">
            <span class="info-label">Código CTB:</span>
            {{ $multa->codigo_ctb }}
        </div>
        <div class="info-row">
            <span class="info-label">Descrição:</span>
            {{ $multa->infracao_descricao }}
        </div>
        <div class="info-row">
            <span class="info-label">Gravidade:</span>
            {{ ucfirst($multa->gravidade) }}
        </div>
        <div class="info-row">
            <span class="info-label">Pontos CNH:</span>
            {{ $multa->pontos_cnh }}
        </div>
        <div class="info-row">
            <span class="info-label">Valor:</span>
            R$ {{ number_format($multa->valor_multa, 2, ',', '.') }}
        </div>
        <div class="info-row">
            <span class="info-label">Data/Hora:</span>
            {{ date('d/m/Y', strtotime($multa->data_infracao)) }} às {{ $multa->hora_infracao }}
        </div>
        <div class="info-row">
            <span class="info-label">Local:</span>
            {{ $multa->local_infracao }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">DADOS DO VEÍCULO</div>
        <div class="info-row">
            <span class="info-label">Placa:</span>
            {{ $multa->placa }}
        </div>
        <div class="info-row">
            <span class="info-label">Marca/Modelo:</span>
            {{ $multa->marca }} {{ $multa->modelo }}
        </div>
        <div class="info-row">
            <span class="info-label">Cor:</span>
            {{ $multa->cor }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">DADOS DO PROPRIETÁRIO</div>
        <div class="info-row">
            <span class="info-label">Nome:</span>
            {{ $multa->proprietario_nome }}
        </div>
        <div class="info-row">
            <span class="info-label">CPF/CNPJ:</span>
            {{ $multa->proprietario_cpf_cnpj }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">AGENTE AUTUADOR</div>
        <div class="info-row">
            <span class="info-label">Nome:</span>
            {{ $multa->agente_nome }}
        </div>
        <div class="info-row">
            <span class="info-label">Matrícula:</span>
            {{ $multa->agente_matricula }}
        </div>
    </div>

    @if(count($evidencias) > 0)
    <div class="section">
        <div class="section-title">EVIDÊNCIAS</div>
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Data Upload</th>
                </tr>
            </thead>
            <tbody>
                @foreach($evidencias as $ev)
                <tr>
                    <td>{{ $ev->tipo }}</td>
                    <td>{{ $ev->descricao ?? '-' }}</td>
                    <td>{{ date('d/m/Y H:i', strtotime($ev->created_at)) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Documento gerado em {{ date('d/m/Y H:i:s') }}</p>
        <p>Este é um documento oficial e possui validade legal</p>
    </div>
</body>
</html>
