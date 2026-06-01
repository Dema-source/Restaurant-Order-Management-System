<?php

namespace App\Services;

use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryService extends BaseService
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
