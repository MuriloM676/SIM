<?php

namespace App\Repositories\Eloquent;

use App\DTOs\Multa\CreateMultaDTO;
use App\DTOs\Multa\MultaFilterDTO;
use App\DTOs\Multa\UpdateMultaDTO;
use App\Models\Multa;
use App\Repositories\Contracts\MultaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MultaRepository implements MultaRepositoryInterface
{
    public function __construct(
        private Multa $model
    ) {}

    public function create(CreateMultaDTO $dto): Multa
    {
        $data = $dto->toArray();
        $data['usuario_criador_id'] = auth()->id();

        return $this->model->create($data);
    }

    public function update(Multa $multa, UpdateMultaDTO $dto): Multa
    {
        $multa->update($dto->toArray());
        return $multa->fresh();
    }

    public function findById(int $id): ?Multa
    {
        return $this->model
            ->with(['municipio', 'infracao', 'veiculo', 'agente', 'usuarioCriador'])
            ->find($id);
    }

    public function findByAutoInfracao(string $autoInfracao): ?Multa
    {
        return $this->model
            ->with(['municipio', 'infracao', 'veiculo', 'agente'])
            ->where('auto_infracao', $autoInfracao)
            ->first();
    }

    public function paginate(MultaFilterDTO $filters): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->with(['municipio', 'infracao', 'veiculo', 'agente', 'usuarioCriador']);

        return $this->applyFilters($query, $filters)
            ->orderBy($filters->sort_by, $filters->sort_direction)
            ->paginate($filters->per_page);
    }

    public function findByMunicipio(int $municipioId, MultaFilterDTO $filters): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->with(['infracao', 'veiculo', 'agente', 'usuarioCriador'])
            ->where('municipio_id', $municipioId);

        return $this->applyFilters($query, $filters)
            ->orderBy($filters->sort_by, $filters->sort_direction)
            ->paginate($filters->per_page);
    }

    public function countByStatus(int $municipioId, string $status): int
    {
        return $this->model
            ->where('municipio_id', $municipioId)
            ->where('status', $status)
            ->count();
    }

    /**
     * Aplica filtros à query
     */
    private function applyFilters($query, MultaFilterDTO $filters)
    {
        if ($filters->municipio_id) {
            $query->where('municipio_id', $filters->municipio_id);
        }

        if ($filters->placa) {
            $query->where('placa', $filters->placa);
        }

        if ($filters->status) {
            $query->where('status', $filters->status->value);
        }

        if ($filters->infracao_id) {
            $query->where('infracao_id', $filters->infracao_id);
        }

        if ($filters->agente_id) {
            $query->where('agente_id', $filters->agente_id);
        }

        if ($filters->data_infracao_inicio) {
            $query->where('data_infracao', '>=', $filters->data_infracao_inicio);
        }

        if ($filters->data_infracao_fim) {
            $query->where('data_infracao', '<=', $filters->data_infracao_fim);
        }

        if ($filters->auto_infracao) {
            $query->where('auto_infracao', 'LIKE', "%{$filters->auto_infracao}%");
        }

        return $query;
    }
}
