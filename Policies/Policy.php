<?php

namespace Lumenite\Backer\Policies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lumenite\Backer\ArchiveTable;
use Lumenite\Backer\BackupDefinition;

/**
 * Policy are the steps to be perform before and after archive is perform.
 * Such as disable foreign key or delete after backup
 *
 * @author Mohammed Mudassir <hello@mudasir.me>
 */
abstract class Policy
{
    /**
     * @return mixed
     */
    abstract public function before();

    /**
     * @return mixed
     */
    abstract public function after();

    /**
     * @param callable $callback
     * @param $definition
     */
    public function apply($definition, callable $callback)
    {
        $this->before($definition);

        $this->after($definition, $callback($definition));
    }
}
