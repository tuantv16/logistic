<?php

namespace App\Services;

use App\Http\Controllers\Traits\OwnerTrait;
use App\Services\Traits\DisplayOrderTrait;

abstract class BaseService
{
    use OwnerTrait, DisplayOrderTrait;

    public function __construct()
    {
    }

}
