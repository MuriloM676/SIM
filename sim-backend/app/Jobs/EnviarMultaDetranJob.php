<?php

namespace App\Jobs;

use App\Enums\MultaStatus;
use App\Models\Multa;
use App\Services\IntegracaoDetranService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job para enviar multa ao Detran de forma assíncrona
 * Com retry automático em caso de falha
 */
class EnviarMultaDetranJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de tentativas
     */
    public $tries = 3;

    /**
     * Tempo entre tentativas (em segundos)
     */
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * Timeout em segundos
     */
    public $timeout = 120;

    public function __construct(
        private Multa $multa
    ) {}

    /**
     * Executa o job
     */
    public function handle(IntegracaoDetranService $integracaoService): void
    {
        Log::info("Enviando multa {$this->multa->auto_infracao} para o Detran");

        try {
            // Envia multa
            $response = $integracaoService->enviarMulta($this->multa);

            // Atualiza status para notificada
            $this->multa->update([
                'status' => MultaStatus::NOTIFICADA->value,
            ]);

            Log::info("Multa {$this->multa->auto_infracao} enviada com sucesso", [
                'response' => $response,
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao enviar multa {$this->multa->auto_infracao} para Detran", [
                'erro' => $e->getMessage(),
                'tentativa' => $this->attempts(),
            ]);

            // Se excedeu tentativas, marca como falha
            if ($this->attempts() >= $this->tries) {
                Log::critical("Multa {$this->multa->auto_infracao} falhou após {$this->tries} tentativas");
                
                // TODO: Notificar gestor sobre a falha
            }

            throw $e; // Relança para retry automático
        }
    }

    /**
     * Trata falha do job
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Job de envio de multa falhou definitivamente", [
            'multa_id' => $this->multa->id,
            'auto_infracao' => $this->multa->auto_infracao,
            'erro' => $exception->getMessage(),
        ]);

        // Poderia reverter status ou notificar equipe
    }
}
