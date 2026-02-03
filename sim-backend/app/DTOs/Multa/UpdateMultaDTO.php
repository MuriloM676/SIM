<?php

namespace App\DTOs\Multa;

/**
 * DTO para atualização de multa
 * Todos os campos opcionais (partial update)
 */
class UpdateMultaDTO
{
    public function __construct(
        public readonly ?int $veiculo_id = null,
        public readonly ?string $placa = null,
        public readonly ?string $data_infracao = null,
        public readonly ?string $hora_infracao = null,
        public readonly ?string $local_infracao = null,
        public readonly ?string $observacoes = null,
        public readonly ?float $velocidade_medida = null,
        public readonly ?float $velocidade_maxima = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            veiculo_id: $data['veiculo_id'] ?? null,
            placa: isset($data['placa']) ? strtoupper($data['placa']) : null,
            data_infracao: $data['data_infracao'] ?? null,
            hora_infracao: $data['hora_infracao'] ?? null,
            local_infracao: $data['local_infracao'] ?? null,
            observacoes: $data['observacoes'] ?? null,
            velocidade_medida: $data['velocidade_medida'] ?? null,
            velocidade_maxima: $data['velocidade_maxima'] ?? null,
        );
    }

    /**
     * Retorna apenas os campos preenchidos
     */
    public function toArray(): array
    {
        return array_filter([
            'veiculo_id' => $this->veiculo_id,
            'placa' => $this->placa,
            'data_infracao' => $this->data_infracao,
            'hora_infracao' => $this->hora_infracao,
            'local_infracao' => $this->local_infracao,
            'observacoes' => $this->observacoes,
            'velocidade_medida' => $this->velocidade_medida,
            'velocidade_maxima' => $this->velocidade_maxima,
        ], fn($value) => !is_null($value));
    }
}
