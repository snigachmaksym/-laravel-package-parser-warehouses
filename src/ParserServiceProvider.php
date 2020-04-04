<?php

namespace Parser\Postal;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ParserServiceProvider extends ServiceProvider
{
    protected $commands = [
        'Parser\Postal\Commands\ParserPostal',
    ];

    public function boot()
    {
        $this->publishes([
            dirname( __DIR__ ).'/config/parser-postal.php' => config_path('parser-postal.php'),
        ]);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('parse:postal')->everyMinute();
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/parser-postal.php','parser-postal'
        );
        $this->commands($this->commands);
    }
}
