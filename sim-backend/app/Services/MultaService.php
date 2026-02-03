<?php

namespace App\Services;

use App\DTOs\Multa\CreateMultaDTO;
use App\DTOs\Multa\MultaFilterDTO;
use App\DTOs\Multa\UpdateMultaDTO;
use App\Enums\MultaStatus;
use App\Exceptions\BusinessException;
use App\Models\Multa;
use App\Repositories\Contracts\MultaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Service que contém a lógica de negócio de multas
 */
class MultaService
{
    public function __construct(
        private MultaRepositoryInterface $multaRepository,
        private WorkflowMultaService $workflowService,
        private AuditoriaService $auditoriaService,
    ) {}

    /**
     * Cria uma nova multa
     */
    public function criar(CreateMultaDTO $dto): Multa
    {
        // Valida regras de negócio
        $this->validarCriacao($dto);

        // Cria a multa
        $multa = $this->multaRepository->create($dto);

        return $multa;
    }

    /**
     * Atualiza uma multa existente
     */
    public function atualizar(int $id, UpdateMultaDTO $dto): Multa
    {
        $multa = $this->buscarPorId($id);

        if (!$multa->podeSerEditada()) {
            throw new BusinessException(
                "Multa no status {$multa->status->label()} não pode ser editada"
            );
        }

        return $this->multaRepository->update($multa, $dto);
    }

    /**
     * Busca multa por ID
     */
    public function buscarPorId(int $id): Multa
    {
        $multa = $this->multaRepository->findById($id);

        if (!$multa) {
            throw new BusinessException('Multa não encontrada');
        }

        // Registra acesso (LGPD)
        $this->auditoriaService->registrarAcessoDadosPessoais(
            'multas',
            $id,
            "Visualização da multa {$multa->auto_infracao}"
        );

        return $multa;
    }

    /**
     * Busca com filtros e paginação
     */
    public function listar(MultaFilterDTO $filters): LengthAwarePaginator
    {
        return $this->multaRepository->paginate($filters);
    }

    /**
     * Lista multas do município do usuário logado
     */
    public function listarPorMunicipio(int $municipioId, MultaFilterDTO $filters): LengthAwarePaginator
    {
        return $this->multaRepository->findByMunicipio($municipioId, $filters);
    }

    /**
     * Transiciona status da multa
     */
    public function mudarStatus(int $id, MultaStatus $novoStatus, ?string $justificativa = null): Multa
    {
        $multa = $this->buscarPorId($id);

        return $this->workflowService->transitarStatus($multa, $novoStatus, $justificativa);
    }

    /**
     * Cancela uma multa
     */
    public function cancelar(int $id, string $justificativa): Multa
    {
        if (empty($justificativa)) {
            throw new BusinessException('Justificativa é obrigatória para cancelamento');
        }

        return $this->mudarStatus($id, MultaStatus::CANCELADA, $justificativa);
    }

    /**
     * Envia multa para o Detran
     */
    public function enviarParaDetran(int $id): Multa
    {
        $multa = $this->buscarPorId($id);

        if ($multa->status !== MultaStatus::REGISTRADA) {
            throw new BusinessException('Apenas multas registradas podem ser enviadas ao Detran');
        }

        // Job assíncrono será disparado pelo WorkflowService
        return $this->mudarStatus($id, MultaStatus::ENVIADA_ORGAO_EXTERNO);
    }

    /**
     * Validações de criação
     */
    private function validarCriacao(CreateMultaDTO $dto): void
    {
        // Validar se o veículo, agente e infração pertencem ao mesmo município
        // (implementar conforme necessário)

        // Validar se a data da infração não é futura
        if (strtotime($dto->data_infracao) > time()) {
            throw new BusinessException('Data da infração não pode ser futura');
        }

        // Se infração requer medidor de velocidade, validar dados
        // (implementar consulta à tabela de infrações)
    }

    /**
     * Estatísticas para dashboard
     */
    public function estatisticasPorMunicipio(int $municipioId): array
    {
        return [
            'total' => $this->multaRepository->countByStatus($municipioId, ''),
            'rascunhos' => $this->multaRepository->countByStatus($municipioId, MultaStatus::RASCUNHO->value),
            'registradas' => $this->multaRepository->countByStatus($municipioId, MultaStatus::REGISTRADA->value),
            'em_recurso' => $this->multaRepository->countByStatus($municipioId, MultaStatus::EM_RECURSO->value),
            'encerradas' => $this->multaRepository->countByStatus($municipioId, MultaStatus::ENCERRADA->value),
        ];
    }
}
