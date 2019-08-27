<?php

namespace Stub\Laravel;

use Stub\Laravel\Console\Parse;
use Stub\Laravel\Console\Create;

use Illuminate\Support\ServiceProvider;

class StubProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Parse::class,
                Create::class,
            ]);
        }
    }

    public function register()
    {
        //
    }
}
