<?php

namespace App\Observers;

use App\Enums\TipoAuditoria;
use App\Models\Multa;
use App\Models\MultaHistorico;
use App\Services\AuditoriaService;

/**
 * Observer para registrar automaticamente mudanças em multas
 */
class MultaObserver
{
    public function __construct(
        private AuditoriaService $auditoriaService
    ) {}

    /**
     * Após criação
     */
    public function created(Multa $multa): void
    {
        $this->auditoriaService->registrar(
            tipoAcao: TipoAuditoria::CRIACAO,
            entidade: 'multas',
            entidadeId: $multa->id,
            descricao: "Multa {$multa->auto_infracao} criada",
            dadosDepois: $multa->toArray()
        );
    }

    /**
     * Antes de atualizar
     */
    public function updating(Multa $multa): void
    {
        // Verifica mudança de status
        if ($multa->isDirty('status')) {
            $statusAntigo = $multa->getOriginal('status');
            $statusNovo = $multa->status->value;

            // Registra no histórico
            MultaHistorico::create([
                'multa_id' => $multa->id,
                'usuario_id' => auth()->id(),
                'status_anterior' => $statusAntigo,
                'status_novo' => $statusNovo,
                'ip' => request()->ip(),
                'data_transicao' => now(),
            ]);
        }
    }

    /**
     * Após atualizar
     */
    public function updated(Multa $multa): void
    {
        // Pega valores originais (antes da atualização)
        $dadosAntigos = [];
        foreach ($multa->getDirty() as $campo => $valorNovo) {
            $dadosAntigos[$campo] = $multa->getOriginal($campo);
        }

        $this->auditoriaService->registrar(
            tipoAcao: TipoAuditoria::ATUALIZACAO,
            entidade: 'multas',
            entidadeId: $multa->id,
            descricao: "Multa {$multa->auto_infracao} atualizada",
            dadosAntes: $dadosAntigos,
            dadosDepois: $multa->getDirty()
        );
    }

    /**
     * Antes de deletar (soft delete)
     */
    public function deleting(Multa $multa): void
    {
        $this->auditoriaService->registrar(
            tipoAcao: TipoAuditoria::EXCLUSAO,
            entidade: 'multas',
            entidadeId: $multa->id,
            descricao: "Multa {$multa->auto_infracao} excluída",
            dadosAntes: $multa->toArray()
        );
    }
}
