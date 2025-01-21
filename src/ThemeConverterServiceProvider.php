<?php

namespace Pankaj\ThemeConverter;

use Pankaj\ThemeConverter\Commands\ThemeConverterCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ThemeConverterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('theme-converter')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_theme_converter_table')
            ->hasCommand(ThemeConverterCommand::class);
    }
}
