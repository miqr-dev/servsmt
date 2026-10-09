<?php

namespace App\Console\Commands;

use App\Support\KorsoAssignments;
use Illuminate\Console\Command;

/** Nightly: un-assign open Korso tickets of users without Korso_ma / Korso_Admin (see KorsoAssignments). */
class KorsoReleaseAssignments extends Command
{
    protected $signature = 'korso:release-assignments {--dry-run : only count, change nothing}';

    protected $description = 'Un-assign open Korso tickets of users who no longer have Korso_ma / Korso_Admin or were deactivated';

    public function handle(): int
    {
        $n = KorsoAssignments::release(null, 'nightly check', (bool) $this->option('dry-run'));
        $this->info(($this->option('dry-run') ? 'Würde ' : '')."{$n} Korso-Ticket(s) ".($this->option('dry-run') ? 'freigeben.' : 'freigegeben.'));

        return self::SUCCESS;
    }
}
