<?php

namespace App\Console\Commands;

use App\Support\InventoryAdoption;
use Illuminate\Console\Command;

/**
 * Old-inventory hallways ("Flur") and printer rooms without an AD room ->
 * rooms in Einstellungen > Standorte & Adressen (see App\Support\InventoryAdoption).
 * Never writes to AD. Run again after every fresh DB copy (go-live).
 */
class AdAdoptInventoryRooms extends Command
{
    protected $signature = 'ad:adopt-inventory-rooms {--dry-run : only show the plan, write nothing}';

    protected $description = 'Add the old inventory hallways and printer rooms to the AD locations (app rooms, AD is not changed)';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $r = InventoryAdoption::run($dry);

        $this->table(['Status', 'Alter Raum', 'Alte Adresse', 'Etage', 'Drucker', 'Ziel / Grund'], array_map(fn ($row) => [
            $row['status'],
            $row['old'],
            $row['old_place'],
            $row['old_floor'],
            implode(', ', $row['printers']) ?: '–',
            $row['target'] ?? $row['reason'],
        ], $r['rows']));

        $this->line(($dry ? 'Plan (nichts geändert): ' : '')
            ."{$r['created']} neue App-Räume, {$r['linked']} mit vorhandenen Räumen verknüpft, {$r['open']} offen.");
        if ($r['open']) {
            $this->line('Offene Räume: auf /ad-inventory/zuordnung von Hand zuordnen, oder die Adresse auf Einstellungen > Standorte & Adressen anlegen.');
        }

        return self::SUCCESS;
    }
}
