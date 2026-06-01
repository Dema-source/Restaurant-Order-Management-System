<?php

namespace App\Services;

use App\Repositories\Contracts\MenuItemRepositoryInterface;

class MenuItemService extends BaseService
{
    public function __construct(MenuItemRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
