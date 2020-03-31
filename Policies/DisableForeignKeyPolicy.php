<?php

namespace Lumenite\Backer\Policies;

use Illuminate\Database\DatabaseManager;
use Lumenite\Backer\ArchiveTable;
use Lumenite\Backer\BackupDefinition;

/**
 * @author Mohammed Mudassir <hello@mudasir.me>
 */
class DisableForeignKeyPolicy extends Policy
{
    /**
     * @return mixed|void
     */
    public function before()
    {
        app(DatabaseManager::class)->statement('SET FOREIGN_KEY_CHECKS=0;');
    }

    /**
     * @return mixed|void
     */
    public function after()
    {
        app(DatabaseManager::class)->statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
