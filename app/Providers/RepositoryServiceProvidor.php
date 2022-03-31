<?php

namespace App\Providers;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
class RepositoryServiceProvidor extends ServiceProvider
{
    public function register(){
        $this->app->bind(
            'App\Interfaces\VerifyInterface',
            'App\Repositories\VerifyRepository'
        );
    }
}
