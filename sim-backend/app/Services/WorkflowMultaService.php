<?php

namespace App\Services;

use App\Enums\MultaStatus;
use App\Enums\TipoAuditoria;
use App\Exceptions\BusinessException;
use App\Jobs\EnviarMultaDetranJob;
use App\Models\Multa;
use App\Models\MultaHistorico;

/**
 * Serviço responsável pelo controle de workflow/estado das multas
 * Garante transições válidas e dispara ações correspondentes
 */
class WorkflowMultaService
{
    public function __construct(
        private AuditoriaService $auditoriaService
    ) {}

    /**
     * Transita o status de uma multa
     */
    public function transitarStatus(
        Multa $multa,
        MultaStatus $novoStatus,
        ?string $justificativa = null
    ): Multa {
        $statusAtual = $multa->status;

        // Valida se a transição é permitida
        if (!$statusAtual->podeTransitarPara($novoStatus)) {
            throw new BusinessException(
                "Transição de {$statusAtual->label()} para {$novoStatus->label()} não é permitida"
            );
        }

        // Registra no histórico
        $this->registrarHistorico($multa, $statusAtual, $novoStatus, $justificativa);

        // Atualiza status
        $multa->update(['status' => $novoStatus]);

        // Executa ações pós-transição
        $this->executarAcoesPosTransicao($multa, $novoStatus);

        // Auditoria
        $this->auditoriaService->registrar(
            tipoAcao: TipoAuditoria::TRANSICAO_STATUS,
            entidade: 'multas',
            entidadeId: $multa->id,
            descricao: "Status alterado de {$statusAtual->label()} para {$novoStatus->label()}",
            dadosAntes: ['status' => $statusAtual->value],
            dadosDepois: ['status' => $novoStatus->value]
        );

        return $multa->fresh();
    }

    /**
     * Registra a transição no histórico
     */
    private function registrarHistorico(
        Multa $multa,
        MultaStatus $statusAntigo,
        MultaStatus $statusNovo,
        ?string $justificativa
    ): void {
        MultaHistorico::create([
            'multa_id' => $multa->id,
            'usuario_id' => auth()->id(),
            'status_anterior' => $statusAntigo->value,
            'status_novo' => $statusNovo->value,
            'justificativa' => $justificativa,
            'ip' => request()->ip(),
            'data_transicao' => now(),
        ]);
    }

    /**
     * Executa ações automáticas após mudança de status
     */
    private function executarAcoesPosTransicao(Multa $multa, MultaStatus $novoStatus): void
    {
        match ($novoStatus) {
            // Ao enviar para órgão externo, dispara job de integração
            MultaStatus::ENVIADA_ORGAO_EXTERNO => $this->enviarParaDetran($multa),

            // Ao notificar, registra data
            MultaStatus::NOTIFICADA => $this->registrarNotificacao($multa),

            // Demais status não requerem ação
            default => null,
        };
    }

    /**
     * Dispara job para enviar multa ao Detran
     */
    private function enviarParaDetran(Multa $multa): void
    {
        EnviarMultaDetranJob::dispatch($multa)->onQueue('integracoes');

        $multa->update(['data_envio_detran' => now()]);
    }

    /**
     * Registra data de notificação
     */
    private function registrarNotificacao(Multa $multa): void
    {
        $multa->update(['data_notificacao' => now()]);
    }

    /**
     * Verifica se multa pode ser editada
     */
    public function podeEditar(Multa $multa): bool
    {
        return $multa->podeSerEditada();
    }

    /**
     * Verifica se multa pode ser cancelada
     */
    public function podeCancelar(Multa $multa): bool
    {
        return !in_array($multa->status, [
            MultaStatus::ENCERRADA,
            MultaStatus::CANCELADA,
        ]);
    }

    /**
     * Retorna próximos status possíveis
     */
    public function obterProximosStatus(Multa $multa): array
    {
        return array_map(
            fn(MultaStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'cor' => $status->cor(),
            ],
            $multa->status->transicoesPossiveis()
        );
    }
}
