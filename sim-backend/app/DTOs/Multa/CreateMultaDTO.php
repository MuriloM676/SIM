<?php

namespace App\DTOs\Multa;

use App\Enums\MultaStatus;

/**
 * DTO para criação de multa
 * Garantimos type-safety e validação em camadas
 */
class CreateMultaDTO
{
    public function __construct(
        public readonly int $municipio_id,
        public readonly int $infracao_id,
        public readonly int $veiculo_id,
        public readonly int $agente_id,
        public readonly string $placa,
        public readonly string $data_infracao,
        public readonly string $hora_infracao,
        public readonly string $local_infracao,
        public readonly ?string $observacoes = null,
        public readonly ?float $velocidade_medida = null,
        public readonly ?float $velocidade_maxima = null,
        public readonly MultaStatus $status = MultaStatus::RASCUNHO,
    ) {}

    /**
     * Cria DTO a partir de array validado
     */
    public static function fromArray(array $data): self
    {
        return new self(
            municipio_id: $data['municipio_id'],
            infracao_id: $data['infracao_id'],
            veiculo_id: $data['veiculo_id'],
            agente_id: $data['agente_id'],
            placa: strtoupper($data['placa']),
            data_infracao: $data['data_infracao'],
            hora_infracao: $data['hora_infracao'],
            local_infracao: $data['local_infracao'],
            observacoes: $data['observacoes'] ?? null,
            velocidade_medida: $data['velocidade_medida'] ?? null,
            velocidade_maxima: $data['velocidade_maxima'] ?? null,
            status: isset($data['status']) ? MultaStatus::from($data['status']) : MultaStatus::RASCUNHO,
        );
    }

    /**
     * Converte para array para persistência
     */
    public function toArray(): array
    {
        return [
            'municipio_id' => $this->municipio_id,
            'infracao_id' => $this->infracao_id,
            'veiculo_id' => $this->veiculo_id,
            'agente_id' => $this->agente_id,
            'placa' => $this->placa,
            'data_infracao' => $this->data_infracao,
            'hora_infracao' => $this->hora_infracao,
            'local_infracao' => $this->local_infracao,
            'observacoes' => $this->observacoes,
            'velocidade_medida' => $this->velocidade_medida,
            'velocidade_maxima' => $this->velocidade_maxima,
            'status' => $this->status->value,
        ];
    }
}
