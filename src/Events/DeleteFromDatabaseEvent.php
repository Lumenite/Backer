<?php

namespace Lumenite\Backer\Events;

use Illuminate\Database\Eloquent\Collection;
use Lumenite\Backer\ArchiveTable;
use Lumenite\Backer\BackupDefinition;

/**
 * @author Mohammed Mudassir <hello@mudasir.me>
 */
class DeleteFromDatabaseEvent
{
    /** @var BackupDefinition $definition */
    protected $definition;

    /** @var Collection $records */
    protected $records;

    /**
     * @param BackupDefinition $definition
     * @param Collection $records
     */
    public function __construct(BackupDefinition $definition, Collection $records)
    {
        $this->definition = $definition;
        $this->records = $records;
    }

    public function handle(ArchiveTable $archiveTable)
    {
        $output = $archiveTable->getOutput();
        $startDate = $this->records->first()->{$this->definition->sortBy};
        $endDate = $this->records->last()->{$this->definition->sortBy};

        $output->writeln("* [deleting] records:{$this->definition->table()} date:{$startDate} <=> {$endDate}.");

        $this->definition->filter()
            ->whereIn('id', $this->records->pluck('id')->toArray())
            ->forceDelete();
    }
}
