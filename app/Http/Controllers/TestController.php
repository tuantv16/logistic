<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TestController extends Controller
{
    public function createAccount() {
        $account = new Account();
        $account->name = 'admin';
        $account->email = 'admin@gmail.com';
        $account->password = Hash::make('123456');
        $account->save();
    }

    public function testApi() {
        echo 'test api thành công';
        die;
    }
    
}
