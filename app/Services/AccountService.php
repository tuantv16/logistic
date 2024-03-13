<?php

namespace App\Services;

use App\Models\Area;
use App\Repositories\Interfaces\AccountRepository;
use App\Repositories\Interfaces\AreaRepository;
use App\Repositories\Interfaces\OccupationRepository;
use Illuminate\Support\Facades\DB;

class AccountService extends BaseService
{

    protected $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public static function getInitData() 
    {
       return [];
    }

    public static function processDataOther($params) 
    {
       // for, if else in here
       return [];
    }
   

}