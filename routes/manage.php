<?php

use App\Http\Controllers\Manage\AccountController;
use Illuminate\Support\Facades\Route;

Route::resource('accounts', AccountController::class, array("as" => "manage.accounts"));