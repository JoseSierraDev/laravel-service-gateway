<?php

namespace LaravelServiceGateway;

use Illuminate\Support\ServiceProvider;
use LaravelServiceGateway\Commands\MakeGatewayCommand;
use LaravelServiceGateway\Commands\MakeModelExtendCommand;
use LaravelServiceGateway\Commands\MakeServiceCommand;


class LaravelServiceGatewayServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeServiceCommand::class,
                MakeGatewayCommand::class,
                MakeModelExtendCommand::class,
            ]);
        }
    }
}

