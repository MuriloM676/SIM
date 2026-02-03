<?php

namespace App\Services;

use App\Enums\TipoAuditoria;
use App\Exceptions\IntegracaoException;
use App\Models\IntegracaoLog;
use App\Models\Multa;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Serviço de integração com API do Detran
 * Simula integração real com órgão externo
 */
class IntegracaoDetranService
{
    private string $baseUrl;
    private string $apiToken;
    private int $timeout;

    public function __construct(
        private AuditoriaService $auditoriaService
    ) {
        $this->baseUrl = config('services.detran.url', env('DETRAN_API_URL'));
        $this->apiToken = config('services.detran.token', env('DETRAN_API_TOKEN'));
        $this->timeout = config('services.detran.timeout', env('DETRAN_API_TIMEOUT', 30));
    }

    /**
     * Envia multa para o Detran
     */
    public function enviarMulta(Multa $multa): array
    {
        $startTime = microtime(true);
        $endpoint = "{$this->baseUrl}/api/v1/multas";

        $payload = $this->montarPayloadMulta($multa);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $payload);

            $tempoResposta = (int) ((microtime(true) - $startTime) * 1000);

            // Log da integração
            $this->registrarLog(
                sistemaExterno: 'detran',
                operacao: 'envio_multa',
                tipo: 'request',
                multaId: $multa->id,
                metodoHttp: 'POST',
                endpoint: $endpoint,
                statusHttp: $response->status(),
                requestBody: $payload,
                responseBody: $response->json(),
                tempoRespostaMs: $tempoResposta,
                sucesso: $response->successful()
            );

            if (!$response->successful()) {
                throw new IntegracaoException(
                    "Erro ao enviar multa para Detran: {$response->body()}"
                );
            }

            $responseData = $response->json();

            // Atualiza multa com número do Detran
            if (isset($responseData['numero_detran'])) {
                $multa->update([
                    'numero_detran' => $responseData['numero_detran'],
                ]);
            }

            // Auditoria
            $this->auditoriaService->registrar(
                tipoAcao: TipoAuditoria::INTEGRACAO_ENVIADA,
                entidade: 'multas',
                entidadeId: $multa->id,
                descricao: "Multa enviada ao Detran com sucesso"
            );

            return $responseData;

        } catch (\Exception $e) {
            $tempoResposta = (int) ((microtime(true) - $startTime) * 1000);

            // Log do erro
            $this->registrarLog(
                sistemaExterno: 'detran',
                operacao: 'envio_multa',
                tipo: 'request',
                multaId: $multa->id,
                metodoHttp: 'POST',
                endpoint: $endpoint,
                statusHttp: null,
                requestBody: $payload,
                responseBody: null,
                tempoRespostaMs: $tempoResposta,
                sucesso: false,
                erro: $e->getMessage()
            );

            Log::error('Erro na integração com Detran', [
                'multa_id' => $multa->id,
                'erro' => $e->getMessage(),
            ]);

            throw new IntegracaoException(
                "Falha na comunicação com Detran: {$e->getMessage()}"
            );
        }
    }

    /**
     * Consulta veículo no Detran
     */
    public function consultarVeiculo(string $placa): array
    {
        $endpoint = "{$this->baseUrl}/api/v1/veiculos/{$placa}";
        $startTime = microtime(true);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Accept' => 'application/json',
                ])
                ->get($endpoint);

            $tempoResposta = (int) ((microtime(true) - $startTime) * 1000);

            $this->registrarLog(
                sistemaExterno: 'detran',
                operacao: 'consulta_veiculo',
                tipo: 'request',
                metodoHttp: 'GET',
                endpoint: $endpoint,
                statusHttp: $response->status(),
                responseBody: $response->json(),
                tempoRespostaMs: $tempoResposta,
                sucesso: $response->successful()
            );

            if (!$response->successful()) {
                throw new IntegracaoException('Veículo não encontrado no Detran');
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Erro ao consultar veículo no Detran', [
                'placa' => $placa,
                'erro' => $e->getMessage(),
            ]);

            throw new IntegracaoException($e->getMessage());
        }
    }

    /**
     * Monta payload para envio de multa
     */
    private function montarPayloadMulta(Multa $multa): array
    {
        return [
            'auto_infracao' => $multa->auto_infracao,
            'codigo_municipio' => $multa->municipio->codigo_ibge,
            'codigo_infracao' => $multa->infracao->codigo_ctb,
            'placa' => $multa->placa,
            'data_infracao' => $multa->data_infracao->format('Y-m-d'),
            'hora_infracao' => $multa->hora_infracao->format('H:i:s'),
            'local' => $multa->local_infracao,
            'latitude' => $multa->latitude,
            'longitude' => $multa->longitude,
            'valor' => $multa->valor_multa,
            'pontos' => $multa->pontos_cnh,
            'agente_matricula' => $multa->agente->matricula,
            'agente_nome' => $multa->agente->nome,
            'velocidade_medida' => $multa->velocidade_medida,
            'velocidade_maxima' => $multa->velocidade_maxima,
            'observacoes' => $multa->observacoes,
        ];
    }

    /**
     * Registra log da integração
     */
    private function registrarLog(
        string $sistemaExterno,
        string $operacao,
        string $tipo,
        string $metodoHttp,
        string $endpoint,
        ?int $statusHttp = null,
        ?int $multaId = null,
        ?array $requestBody = null,
        ?array $responseBody = null,
        ?int $tempoRespostaMs = null,
        bool $sucesso = false,
        ?string $erro = null
    ): void {
        IntegracaoLog::create([
            'sistema_externo' => $sistemaExterno,
            'operacao' => $operacao,
            'tipo' => $tipo,
            'multa_id' => $multaId,
            'usuario_id' => auth()->id(),
            'metodo_http' => $metodoHttp,
            'endpoint' => $endpoint,
            'status_http' => $statusHttp,
            'request_body' => $requestBody,
            'response_body' => $responseBody,
            'tempo_resposta_ms' => $tempoRespostaMs,
            'sucesso' => $sucesso,
            'erro' => $erro,
        ]);
    }
}
