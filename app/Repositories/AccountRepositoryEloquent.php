<?php

namespace App\Repositories;

use App\Models\Account;
use App\Repositories\Interfaces\AccountRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Prettus\Validator\Exceptions\ValidatorException;
use Utility;
use Password;

class AccountRepositoryEloquent extends BaseRepositoryEloquent implements AccountRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return Account::class;
    }

    /**
     * create unique account
     *
     * @param $data
     * @return LengthAwarePaginator|Collection|mixed
     * @throws ValidatorException
     */
    public function getRandomAccount($number): mixed
    {
        return $this->inRandomOrder()->limit($number)->get();
    }


    /**
     * @describe check exists account
     * @param $clientId
     * @param $username
     * @return bool
     */
    public function checkExistAccount($username, $clientId, $type = ''): bool
    {
        // query data
        // $query = $this->model;
        // if ($clientId){
        //     $query = $query->where('type_id', '<>', $clientId);
        // }

        // if($type??false) {
        //     $query = $query->where('type', $type);
        // }

        // $query = $query->where('username', $username);
        // return $query->count() > 0;
    }
}
