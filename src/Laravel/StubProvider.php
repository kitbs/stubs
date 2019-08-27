<?php

namespace Stub\Laravel;

use Stub\Laravel\Console\Create;
use Stub\Laravel\Console\Render;
use Illuminate\Support\ServiceProvider;

class StubProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Render::class,
                Create::class,
            ]);
        }
    }

    public function register()
    {
        //
    }
}
