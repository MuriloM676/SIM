<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EnviarMultaDetran implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // 3 tentativas
    public $backoff = [60, 300, 900]; // Backoff exponencial: 1min, 5min, 15min

    public function __construct(
        public int $multaId
    ) {}

    public function handle(): void
    {
        $multa = DB::table('multas')
            ->join('veiculos', 'multas.veiculo_id', '=', 'veiculos.id')
            ->join('infracoes', 'multas.infracao_id', '=', 'infracoes.id')
            ->join('agentes', 'multas.agente_id', '=', 'agentes.id')
            ->select(
                'multas.*',
                'veiculos.placa',
                'veiculos.renavam',
                'infracoes.codigo_ctb',
                'agentes.matricula as agente_matricula'
            )
            ->where('multas.id', $this->multaId)
            ->first();

        if (!$multa) {
            Log::error("Multa {$this->multaId} não encontrada para envio ao Detran");
            return;
        }

        $payload = [
            'auto_infracao' => $multa->auto_infracao,
            'placa' => $multa->placa,
            'renavam' => $multa->renavam,
            'codigo_infracao' => $multa->codigo_ctb,
            'data_infracao' => $multa->data_infracao,
            'hora_infracao' => $multa->hora_infracao,
            'local_infracao' => $multa->local_infracao,
            'valor_multa' => $multa->valor_multa,
            'pontos_cnh' => $multa->pontos_cnh,
            'agente_matricula' => $multa->agente_matricula,
        ];

        try {
            // Simulação de integração (em produção seria endpoint real do Detran)
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('DETRAN_API_TOKEN'),
                    'Content-Type' => 'application/json',
                ])
                ->post(env('DETRAN_API_URL', 'http://api.detran.simulado/multas'), $payload);

            if ($response->successful()) {
                // Atualizar status da multa
                DB::table('multas')->where('id', $this->multaId)->update([
                    'status' => 'enviada_orgao_externo',
                    'protocolo_externo' => $response->json('protocolo'),
                    'data_envio_detran' => now(),
                    'updated_at' => now(),
                ]);

                // Log de integração
                DB::table('logs_integracao')->insert([
                    'entidade' => 'Multa',
                    'entidade_id' => $this->multaId,
                    'tipo' => 'envio_detran',
                    'endpoint' => env('DETRAN_API_URL'),
                    'payload_envio' => json_encode($payload),
                    'payload_resposta' => $response->body(),
                    'status_http' => $response->status(),
                    'sucesso' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info("Multa {$this->multaId} enviada ao Detran com sucesso");

            } else {
                throw new \Exception("Erro HTTP {$response->status()}: {$response->body()}");
            }

        } catch (\Exception $e) {
            // Log de erro
            DB::table('logs_integracao')->insert([
                'entidade' => 'Multa',
                'entidade_id' => $this->multaId,
                'tipo' => 'envio_detran',
                'endpoint' => env('DETRAN_API_URL'),
                'payload_envio' => json_encode($payload),
                'payload_resposta' => $e->getMessage(),
                'status_http' => 0,
                'sucesso' => false,
                'tentativa' => $this->attempts(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::error("Erro ao enviar multa {$this->multaId} ao Detran (tentativa {$this->attempts()}): {$e->getMessage()}");

            // Se esgotou tentativas
            if ($this->attempts() >= $this->tries) {
                DB::table('multas')->where('id', $this->multaId)->update([
                    'erro_integracao' => $e->getMessage(),
                    'updated_at' => now(),
                ]);
            }

            throw $e; // Relaça exceção para retry automático
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Job EnviarMultaDetran falhou definitivamente para multa {$this->multaId}: {$exception->getMessage()}");
        
        DB::table('multas')->where('id', $this->multaId)->update([
            'status' => 'erro_envio',
            'erro_integracao' => $exception->getMessage(),
            'updated_at' => now(),
        ]);
    }
}
