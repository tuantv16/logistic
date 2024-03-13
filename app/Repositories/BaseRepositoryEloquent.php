<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class UserRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
abstract class BaseRepositoryEloquent extends BaseRepository
{

    /**
     * @return int
     */
    public function getMaxDispOrder(): int
    {
        try {
            return $this->model->max('disp_order');
        } catch (\Exception $e) {
            return 0;
        }
    }


}
