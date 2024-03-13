<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\AccountRepository;
use App\Services\AccountService;
use Illuminate\Http\Request;

class AccountController extends Controller
{

    protected $accountRepository;
    protected $accountService;

    public function __construct(AccountRepository $accountRepository, AccountService $accountService)
    {
        $this->accountRepository = $accountRepository;
        $this->accountService = $accountService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all data
        // 1
        $data1 = $this->accountRepository->all();
        $data2 = $this->accountRepository->find(1);
        
        // 2
        $data3 = $this->accountRepository->getRandomAccount(3);

        //3
        $params = [
            // ...
        ];
        $data4 = $this->accountService->processDataOther($params);

        // response - Coding....
        // ...
        // Đang code dang dở

        //return 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
