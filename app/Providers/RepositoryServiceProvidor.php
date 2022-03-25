<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
class RepositoryServiceProvidor extends ServiceProvider
{
    public function register(){
        $this->app->bind(
            'App\Interfaces\VerifyInterface',
            'App\Repositories\VerifyRepository'
        );
    }
}
