<?php

namespace App\Repositories;

use App\Models\MenuItem;
use App\Repositories\Contracts\MenuItemRepositoryInterface;

class MenuItemRepository extends BaseRepository implements MenuItemRepositoryInterface
{
    public function __construct(MenuItem $model)
    {
        parent::__construct($model);
    }
}
