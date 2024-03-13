<?php

namespace App\Repositories\Interfaces;

interface AccountRepository extends BaseRepository
{
    /**
     * get random account
     *
     * @param $data
     * @return mixed
     */
    public function getRandomAccount($number): mixed;

     /**
     * get exist account
     *
     * @param $username, $clientId, $type 
     * @return mixed
     */
    public function checkExistAccount($username, $clientId, $type = '');
}
