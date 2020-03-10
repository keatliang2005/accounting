<?php

namespace Scottlaurent\Accounting;

use Illuminate\Support\ServiceProvider;

class AccountingServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/migrations' => database_path('migrations')], 'migrations');
        }

        //php artisan vendor:publish --provider="Scottlaurent\Accounting\AccountingServiceProvider"
        // Load Items
        //$this->loadMigrationsFrom(__DIR__ . '/migrations');
    }
}