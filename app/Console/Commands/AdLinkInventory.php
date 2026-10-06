<?php

namespace App\Console\Commands;

use App\Support\AdInventoryLinker;
use Illuminate\Console\Command;

/** Link inv_rooms / inv_items to the imported AD rooms / computers (see AdInventoryLinker). */
class AdLinkInventory extends Command
{
    protected $signature = 'ad:link-inventory {--dry-run : only count, write nothing}';

    protected $description = 'Link the old inventory (inv_rooms, inv_items) to ad_ous / ad_computers';

    public function handle(): int
    {
        $s = AdInventoryLinker::run((bool) $this->option('dry-run'));
        $this->table(['', 'zugeordnet', 'nicht zugeordnet'], [
            ['Räume (inv_rooms)', $s['rooms_linked'], $s['rooms_unlinked']],
            ['Computer (inv_items: Server/PC/Laptop)', $s['items_linked'], $s['items_unlinked']],
        ]);
        $this->line('Manuelle Zuordnungen bleiben unverändert. Nicht zugeordnete: /ad-inventory/zuordnung');
        if (! $this->option('dry-run')) {
            foreach (AdInventoryLinker::backfillTickets() as $col => $n) {
                $this->line("{$col}: {$n} Tickets aktualisiert");
            }
        }

        return self::SUCCESS;
    }
}
