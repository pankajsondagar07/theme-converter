<?php

namespace Pankaj\ThemeConverter;

use Illuminate\Support\ServiceProvider;
use Pankaj\ThemeConverter\Commands\ConvertThemeCommand;

class ThemeConverterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ConvertThemeCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/theme-converter.php' => config_path('theme-converter.php'),
        ], 'config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'theme-converter');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/theme-converter.php', 'theme-converter'
        );

        $this->app->singleton('theme-converter', function () {
            return new ThemeConverter();
        });
    }
}