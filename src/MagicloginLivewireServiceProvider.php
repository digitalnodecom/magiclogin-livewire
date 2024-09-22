<?php

namespace Digitalnode\MagicloginLivewire;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Digitalnode\MagicloginLivewire\Console\MagicloginLivewireCommand;

class MagicloginLivewireServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('magiclogin-livewire')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_magiclogin_livewire_table')
            ->hasCommand(MagicloginLivewireCommand::class);
    }
}
