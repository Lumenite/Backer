<?php

namespace Lumenite\Backer\Commands;

use App\Backup\BackupLeadUserDefinition;
use App\Backup\ImportedUser\BackupImportedUserDefinition;
use App\Profile;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * @author Mohammed Mudassir <hello@mudasir.me>
 */
class SoftDeleteTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backer:soft-delete {definition}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft delete the records.';

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $startDate = Carbon::parse(config('backer.backup_start_date')());
        $endDate =  Carbon::parse(config('backer.backup_end_date')());
        $definition = config("backer.soft_delete.{$this->argument('definition')}");
        $this->output->caution("Deleting user from $startDate to $endDate");

        $users = (new $definition($endDate, $startDate))
            ->filter()
            ->where('deleted_at', null);

        if ($this->confirm("{$users->count()} users will be soft deleted.")) {
            $users->delete();

            Profile::whereIn('user_id', $users->pluck('id'))->delete();
        }
    }
}
