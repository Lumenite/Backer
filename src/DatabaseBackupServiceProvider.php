<?php

namespace Lumenite\Backer;

use Illuminate\Support\ServiceProvider;
use Lumenite\Backer\Commands\SoftDeleteTableCommand;

/**
 * @author Mohammed Mudassir <hello@mudasir.me>
 */
class DatabaseBackupServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            BackupTableCommand::class,
            SoftDeleteTableCommand::class,
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }
}
