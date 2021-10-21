<?php

namespace Zepson\Dpo;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Zepson\Dpo\Commands\DpoCommand;

class DpoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('dpo-laravel')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('2021_10_20_222228_create_direct_pays_table')
            ->hasCommand(DpoCommand::class);
    }
}
