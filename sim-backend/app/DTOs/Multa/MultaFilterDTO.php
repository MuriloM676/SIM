<?php

namespace App\DTOs\Multa;

use App\Enums\MultaStatus;

/**
 * DTO para filtros de busca de multas
 * Usado em relatórios e listagens
 */
class MultaFilterDTO
{
    public function __construct(
        public readonly ?int $municipio_id = null,
        public readonly ?string $placa = null,
        public readonly ?MultaStatus $status = null,
        public readonly ?int $infracao_id = null,
        public readonly ?int $agente_id = null,
        public readonly ?string $data_infracao_inicio = null,
        public readonly ?string $data_infracao_fim = null,
        public readonly ?string $auto_infracao = null,
        public readonly int $per_page = 15,
        public readonly string $sort_by = 'created_at',
        public readonly string $sort_direction = 'desc',
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            municipio_id: $data['municipio_id'] ?? null,
            placa: isset($data['placa']) ? strtoupper($data['placa']) : null,
            status: isset($data['status']) ? MultaStatus::from($data['status']) : null,
            infracao_id: $data['infracao_id'] ?? null,
            agente_id: $data['agente_id'] ?? null,
            data_infracao_inicio: $data['data_infracao_inicio'] ?? null,
            data_infracao_fim: $data['data_infracao_fim'] ?? null,
            auto_infracao: $data['auto_infracao'] ?? null,
            per_page: $data['per_page'] ?? 15,
            sort_by: $data['sort_by'] ?? 'created_at',
            sort_direction: $data['sort_direction'] ?? 'desc',
        );
    }

    /**
     * Retorna apenas filtros ativos
     */
    public function activeFilters(): array
    {
        return array_filter([
            'municipio_id' => $this->municipio_id,
            'placa' => $this->placa,
            'status' => $this->status?->value,
            'infracao_id' => $this->infracao_id,
            'agente_id' => $this->agente_id,
            'data_infracao_inicio' => $this->data_infracao_inicio,
            'data_infracao_fim' => $this->data_infracao_fim,
            'auto_infracao' => $this->auto_infracao,
        ], fn($value) => !is_null($value));
    }
}
