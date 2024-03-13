<?php

namespace App\Repositories\Interfaces;

use Prettus\Repository\Contracts\RepositoryInterface;

interface BaseRepository extends RepositoryInterface
{
    public function getMaxDispOrder();

}
