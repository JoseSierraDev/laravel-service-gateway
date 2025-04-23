<?php

namespace LaravelServiceGateway;

use Illuminate\Support\ServiceProvider;
use LaravelServiceGateway\Commands\MakeGatewayCommand;
use LaravelServiceGateway\Commands\MakeModelExtendCommand;
use LaravelServiceGateway\Commands\MakeServiceCommand;
use Illuminate\Foundation\Application;


class LaravelServiceGatewayServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->extend('command.model.make', function ($command, $app) {
            return $app->make(MakeModelExtendCommand::class);
        });

        $this->commands([
            MakeServiceCommand::class,
            MakeGatewayCommand::class,
            MakeModelExtendCommand::class,
        ]);
    }

}

