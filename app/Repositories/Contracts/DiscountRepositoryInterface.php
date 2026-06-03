<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DiscountRepositoryInterface extends RepositoryInterface
{
    public function getPaginatedWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function toggleActive(int $id): bool;
}
