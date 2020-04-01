<?php

namespace Lumenite\Backer;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemManager;

class BackupTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backer:backup {definition}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run backup command.';

    /**
     * @param FilesystemManager $fileManager
     * @throws \Exception
     */
    public function handle(FilesystemManager $fileManager)
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        $definition = config("backer.definitions.{$this->argument('definition')}");

        $this->info("Backing up from $startDate to $endDate");

        $archiveTable = $this->getArchiveTableInstance($fileManager);

        foreach ($definition as $primaryDefinition => $secondaryDefinitions) {
            if (is_array($secondaryDefinitions)) {
                foreach ($secondaryDefinitions as $secondaryDefinition) {
                    $archiveTable->handle(new $secondaryDefinition($endDate, $startDate));
                }
            }

            if (is_string($secondaryDefinitions)) {
                $archiveTable->handle(new $secondaryDefinitions($endDate, $startDate));
            }

            if (is_string($primaryDefinition)) {
                $archiveTable->handle(new $primaryDefinition($endDate, $startDate));
            }
        }
    }

    /**
     * @return mixed
     */
    protected function getStartDate()
    {
        // @todo If date is not older than 6 months show the warning.

        return Carbon::parse(config('backer.backup_start_date')());
    }

    /**
     * @return mixed
     */
    protected function getEndDate()
    {
        // @todo If date is not older than 6 months show the warning.

        return Carbon::parse(config('backer.backup_end_date')());
    }

    /**
     * @param BackupDefinition $definition
     * @param ArchiveTable $archiveTable
     * @throws \Exception
     */
    protected function applyPolicyAndArchive(BackupDefinition $definition, ArchiveTable $archiveTable)
    {
        $archiveTable->handle($definition);
    }

    /**
     * @param FilesystemManager $fileManager
     * @return ArchiveTable
     */
    protected function getArchiveTableInstance(FilesystemManager $fileManager): ArchiveTable
    {
        return new ArchiveTable(
            $fileManager->disk(config('backer.storage', config('filesystem.default'))),
            $this->getOutput()
        );
    }
}
