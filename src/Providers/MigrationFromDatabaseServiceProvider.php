<?php

namespace Laravel\MigrationFromDatabase\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\MigrationFromDatabase\Console\Commands\ConvertToMigrations;

class MigrationFromDatabaseServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->commands([
            ConvertToMigrations::class,
        ]);
    }

}
