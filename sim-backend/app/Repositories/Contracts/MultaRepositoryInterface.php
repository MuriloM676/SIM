<?php

namespace App\Repositories\Contracts;

use App\DTOs\Multa\CreateMultaDTO;
use App\DTOs\Multa\MultaFilterDTO;
use App\DTOs\Multa\UpdateMultaDTO;
use App\Models\Multa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MultaRepositoryInterface
{
    public function create(CreateMultaDTO $dto): Multa;
    public function update(Multa $multa, UpdateMultaDTO $dto): Multa;
    public function findById(int $id): ?Multa;
    public function findByAutoInfracao(string $autoInfracao): ?Multa;
    public function paginate(MultaFilterDTO $filters): LengthAwarePaginator;
    public function findByMunicipio(int $municipioId, MultaFilterDTO $filters): LengthAwarePaginator;
    public function countByStatus(int $municipioId, string $status): int;
}
