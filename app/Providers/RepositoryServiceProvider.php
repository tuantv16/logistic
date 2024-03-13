<?php

namespace App\Providers;

use App\Repositories\AccessCountRepositoryEloquent;
use App\Repositories\AccountRepositoryEloquent;
use App\Repositories\Interfaces\AccountRepository;
use App\Repositories\Interfaces\AreaRepository;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AccountRepository::class, AccountRepositoryEloquent::class);     
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}