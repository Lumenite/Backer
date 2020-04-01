<?php

namespace Lumenite\Backer;

use Carbon\Carbon;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Lumenite\Backer\Events\DeleteFromDatabaseEvent;

/**
 * @author Mohammed Mudassir <hello@mudasir.me>
 */
class ArchiveTable
{
    /** @var Filesystem $filesystem */
    protected $filesystem;

    /** @var OutputStyle $output */
    protected $output;

    /** @var Builder|Model $table */
    protected $table;

    /**
     * @param Filesystem $filesystem
     * @param OutputStyle $output
     */
    public function __construct(Filesystem $filesystem, OutputStyle $output)
    {
        $this->filesystem = $filesystem;
        $this->output = $output;
    }

    /**
     * @param BackupDefinition $definition
     * @return $this|bool
     * @throws \Exception
     */
    public function handle(BackupDefinition $definition)
    {
        $this->output->caution("Backing up {$definition->table()} by using " . get_class($definition));
        $this->table = $definition->filter();
        $chunkSize = config('backer.chunk_size');
        $chunks = ceil(($totalRecords = $this->table->count()) / $chunkSize);

        if (! $totalRecords) {
            $this->output->writeln("* No record found for {$definition->table()}.");

            return true;
        }

        $this->output->writeln("* [archiving] table:{$definition->table()} records:{$totalRecords} chunk-size:$chunkSize");

        foreach (range(1, $chunks) as $chunk) {
            foreach ($policies = $definition->policies() as $policy) {
                $this->output->writeln("* [policy] applying.");
                $policy->before();
            }

            $records = (clone $this->table)
                ->limit($chunkSize)
                ->orderBy($definition->sortBy)
                ->get();

            $this->output->writeln("* [processing] table:{$definition->table()} size:{$records->count()} chunk:$chunk");

            app('db')->beginTransaction();

            if ($this->archive($definition, clone $records, $chunk)) {
                if ($definition->deleteAfterArchive) {
                    $event = new DeleteFromDatabaseEvent($definition, clone $records);
                    $event->handle($this);
                }
            }

            app('db')->commit();

            foreach ($policies as $policy) {
                $this->output->writeln("* [policy] reverting.");
                $policy->after();
            }
        }

        return $this;
    }

    /**
     * @param BackupDefinition $backupDefinition
     * @param Collection $records
     * @param $chunk
     * @return bool
     * @throws \Exception
     */
    protected function archive(BackupDefinition $backupDefinition, Collection $records, $chunk)
    {
        $startDate = Carbon::parse($records->first()->{$backupDefinition->sortBy});
        $endDate = Carbon::parse($records->last()->{$backupDefinition->sortBy});
        $directory = strtolower(now()->format('M-Y')) . DIRECTORY_SEPARATOR . $backupDefinition->table();
        $filename = "{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}_{$chunk}.json";

        $backupSetting = $this->getDatabaseSettingTable();

        if ($backupSetting->where([
            'entity' => $backupDefinition->model(),
            'status' => BackupStatus::COMPLETE,
            'filename' => $filename,
        ])->first()) {
            if (! $this->confirmBackupExistence($backupDefinition, $filename)) {
                return true;
            }
        }

        $backupSetting->fill([
            'entity' => $backupDefinition->model(),
            'status' => BackupStatus::PENDING,
            'filename' => $filename,
            'logs' => json_encode([
                'ids' => $records->pluck('id')->toArray()
            ]),
            'from' => $startDate,
            'to' => $endDate,
            'total' => $records->count(),
        ])->save();

        if (! $this->filesystem->exists($directory)) {
            $this->filesystem->makeDirectory($directory);
        }

        $this->filesystem->put("$directory/$filename", $records->toJson());

        $backupSetting->update([
            'status' => BackupStatus::COMPLETE,
        ]);

        return true;
    }

    /**
     * @return BackupSetting
     */
    public function getDatabaseSettingTable()
    {
        return (new BackupSetting)->setConnection(
            config('backer.connection', config('database.default'))
        );
    }

    /**
     * @return Builder|Model
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return OutputStyle
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param BackupDefinition $backupDefinition
     * @param string $filename
     * @return bool|mixed
     */
    protected function confirmBackupExistence(BackupDefinition $backupDefinition, string $filename)
    {
        return $this->output->confirm(
            "Backup already exists {$backupDefinition->model()}: {$filename}. Overwrite backup?"
        );
    }
}
