<?php

namespace App\Console\Commands;

use App\AdComputer;
use App\AdOu;
use Illuminate\Console\Command;
use LdapRecord\Models\ActiveDirectory\Computer;
use LdapRecord\Models\ActiveDirectory\OrganizationalUnit;

/**
 * Imports rooms (AD OUs) and computers from Active Directory with LdapRecord
 * into ad_ous / ad_computers (2026-10-05). Matching is by objectGUID, so a
 * renamed or moved OU/computer updates its row instead of creating a new one.
 * Objects no longer in AD are soft-deleted; if they come back they are restored.
 *
 *   php artisan ad:import-inventory --dry-run   show what would happen, write nothing
 *   php artisan ad:import-inventory             import
 *
 * Each OU also gets a type (classify(), 2026-10-05):
 *   Standort Dresden > Computer > Löscherstr. -16 (DD1) > 3.OG > 3.05 |4115 > Teilnehmer-TCs
 *   standort           container  adresse                 etage  raum         gruppe
 * Room labels drop the " |4115" suffix ("3.05"). Each computer gets its Raum
 * (also when it sits in Teilnehmer-/Dozenten-TCs), group and Standort.
 * Top-level OUs that are not "Standort ..." (EDV, DPI, 100_Inaktiv, ...) and
 * everything below them are "other".
 *
 * Base / connection: config/ad_inventory.php (AD_INVENTORY_BASE, AD_INVENTORY_CONNECTION).
 * Scheduled every 6 hours in Console\Kernel. Reads AD only - never writes to AD.
 */
class AdImportInventory extends Command
{
    protected $signature = 'ad:import-inventory
        {--base= : search base DN (default: config ad_inventory.base)}
        {--connection= : LDAP connection (default: config ad_inventory.connection)}
        {--dry-run : only show the counts, write nothing}';

    protected $description = 'Import OUs (rooms) and computers from Active Directory into ad_ous / ad_computers';

    private int $created = 0;
    private int $updated = 0;
    private int $restored = 0;

    public function handle(): int
    {
        $base = $this->option('base') ?: config('ad_inventory.base');
        $conn = $this->option('connection') ?: config('ad_inventory.connection');
        $dry = (bool) $this->option('dry-run');
        $now = now();

        $this->info("AD lesen ({$conn}): {$base}");
        $ous = collect(OrganizationalUnit::on($conn)->in($base)
            ->select(['ou', 'distinguishedname', 'description', 'objectguid'])
            ->paginate(1000))
            // the base OU itself is not a room
            ->reject(fn ($o) => $this->norm($o->getDn()) === $this->norm($base))
            // parents first, so parent_id can be resolved
            ->sortBy(fn ($o) => count($this->parts($o->getDn())))->values();
        $computers = collect(Computer::on($conn)->in($base)
            ->select(['cn', 'distinguishedname', 'dnshostname', 'operatingsystem', 'operatingsystemversion',
                'description', 'useraccountcontrol', 'lastlogontimestamp', 'whencreated', 'objectguid'])
            ->paginate(1000));
        $this->line("Gefunden: {$ous->count()} OUs, {$computers->count()} Computer");

        // safety: an empty result (wrong base, AD hiccup) must not soft-delete everything
        if ($ous->isEmpty() && $computers->isEmpty()) {
            $this->error('Nichts im AD gefunden - Abbruch, es wird nichts geändert. Base / Verbindung prüfen.');

            return self::FAILURE;
        }

        if ($dry) {
            $this->dryRunReport($ous, $computers);

            return self::SUCCESS;
        }

        // --- OUs ---
        $baseDepth = count($this->parts($base));
        $ouIdByDn = [];
        $seenOus = [];
        foreach ($ous as $o) {
            $dn = $o->getDn();
            $row = $this->upsert(AdOu::class, $o->getConvertedGuid(), [
                'name' => (string) $o->getFirstAttribute('ou'),
                'dn' => $dn,
                'parent_id' => $ouIdByDn[$this->norm($this->parentDn($dn))] ?? null,
                'depth' => max(0, count($this->parts($dn)) - $baseDepth - 1),
                'description' => $o->getFirstAttribute('description'),
                'synced_at' => $now,
            ]);
            $ouIdByDn[$this->norm($dn)] = $row->id;
            $seenOus[] = $row->id;
        }
        $ouStats = [$this->created, $this->updated, $this->restored];
        // only imported OUs - rooms added in the app (source 'app') are never touched
        $ouGone = AdOu::where('source', 'ad')->whereNotIn('id', $seenOus ?: [0])->delete();
        $tree = $this->classify();

        // --- computers ---
        $this->created = $this->updated = $this->restored = 0;
        $seenComputers = [];
        foreach ($computers as $c) {
            $dn = $c->getDn();
            $row = $this->upsert(AdComputer::class, $c->getConvertedGuid(), [
                'name' => (string) $c->getFirstAttribute('cn'),
                'dn' => $dn,
                'ad_ou_id' => $ouId = $ouIdByDn[$this->norm($this->parentDn($dn))] ?? null,
                'room_ou_id' => $tree[$ouId]['room'] ?? null,
                'standort_ou_id' => $tree[$ouId]['standort'] ?? null,
                'group' => $tree[$ouId]['group'] ?? null,
                'dns_hostname' => $c->getFirstAttribute('dnshostname'),
                'os' => $c->getFirstAttribute('operatingsystem'),
                'os_version' => $c->getFirstAttribute('operatingsystemversion'),
                'description' => $c->getFirstAttribute('description'),
                'enabled' => ! ((int) $c->getFirstAttribute('useraccountcontrol') & 2),
                'last_logon_at' => $this->date($c->getFirstAttribute('lastlogontimestamp')),
                'ad_created_at' => $this->date($c->getFirstAttribute('whencreated')),
                'synced_at' => $now,
            ]);
            $seenComputers[] = $row->id;
        }
        $pcGone = AdComputer::whereNotIn('id', $seenComputers ?: [0])->delete();

        $this->table(['', 'neu', 'aktualisiert', 'wiederhergestellt', 'nicht mehr im AD (gelöscht)'], [
            ['OUs / Räume', $ouStats[0], $ouStats[1], $ouStats[2], $ouGone],
            ['Computer', $this->created, $this->updated, $this->restored, $pcGone],
        ]);

        $types = AdOu::query()->selectRaw('type, count(*) as n')->groupBy('type')->pluck('n', 'type');
        $this->table(['OU-Typ', 'Anzahl'], collect(['standort', 'adresse', 'etage', 'raum', 'gruppe', 'container', 'other'])
            ->map(fn ($t) => [$t, $types[$t] ?? 0])->all());
        $withRoom = AdComputer::whereNotNull('room_ou_id')->count();
        $this->line("Computer mit Raum: {$withRoom}, ohne Raum: ".(AdComputer::count() - $withRoom));
        // link the old inventory (inv_rooms / inv_items) to the new data
        $link = \App\Support\AdInventoryLinker::run();
        \App\Support\AdInventoryLinker::backfillTickets();
        $this->line("Inventar verknüpft: {$link['rooms_linked']} Räume, {$link['items_linked']} Computer "
            ."(nicht zugeordnet: {$link['rooms_unlinked']} Räume, {$link['items_unlinked']} Computer)");

        // check: every OU below the top level must have found its parent
        $orphans = AdOu::where('source', 'ad')->where('depth', '>', 0)->whereNull('parent_id')->get(['dn']);
        if ($orphans->isNotEmpty()) {
            $this->warn("{$orphans->count()} OUs ohne Eltern-OU, z. B.: ".$orphans->take(3)->pluck('dn')->join(' | '));
        }

        return self::SUCCESS;
    }

    /**
     * Sets type / label / standort_ou_id on every OU (top-down) and returns
     * [ouId => ['room' => id|null, 'standort' => id|null, 'group' => string|null]]
     * for placing the computers.
     */
    private function classify(): array
    {
        $ous = AdOu::orderBy('depth')->get()->keyBy('id');
        $info = [];
        $isContainer = fn ($name) => (bool) preg_match('/^(computer|computers|clients?|rechner)$/iu', trim($name));
        // Standorte that have a "Computer" container: only that branch holds
        // addresses/rooms, their other children (users, groups, ...) are "other".
        $hasContainer = $ous->filter(fn ($o) => $isContainer($o->name))->pluck('parent_id')->filter()->flip();

        foreach ($ous as $ou) {
            $parent = $ou->parent_id ? ($info[$ou->parent_id] ?? null) : null;

            // rooms added in the app: always a Raum, keep their name
            if ($ou->source === 'app') {
                $info[$ou->id] = ['type' => 'raum', 'standort' => $parent['standort'] ?? null, 'room' => $ou->id, 'group' => null];
                if ($ou->type !== 'raum' || $ou->standort_ou_id !== ($parent['standort'] ?? null)) {
                    $ou->forceFill(['type' => 'raum', 'label' => $ou->name, 'standort_ou_id' => $parent['standort'] ?? null])->saveQuietly();
                }
                continue;
            }
            $name = trim($ou->name);
            $label = $name;

            if (! $parent) {
                // top level: "Standort Dresden" -> Standort, anything else -> other
                if (preg_match('/^Standort\s+(.+)$/iu', $name, $m)) {
                    $type = 'standort';
                    $label = trim($m[1]);
                } else {
                    $type = 'other';
                }
            } elseif ($parent['type'] === 'other') {
                $type = 'other';
            } elseif ($parent['type'] === 'standort' && $isContainer($name)) {
                $type = 'container';
            } elseif ($parent['type'] === 'standort' && $hasContainer->has($ou->parent_id)) {
                $type = 'other';
            } elseif (preg_match('/(teilnehmer|dozent)/iu', $name, $m)) {
                $type = 'gruppe';
                $label = mb_stripos($m[1], 'dozent') !== false ? 'Dozent' : 'Teilnehmer';
            } elseif (preg_match('/^(\d+\s*\.?\s*OG|EG|UG|KG|DG|Erdgeschoss|Keller|Dachgeschoss)$/iu', $name)) {
                $type = 'etage';
            } elseif (in_array($parent['type'], ['standort', 'container'], true)) {
                $type = 'adresse';
            } elseif (in_array($parent['type'], ['etage', 'adresse'], true)) {
                $type = 'raum';
                $label = trim(preg_replace('/\s*\|.*$/u', '', $name)); // "3.05 |4115" -> "3.05"
            } else {
                $type = 'other';                    // below a room/group: not expected
            }

            $standortId = $type === 'standort' ? $ou->id : ($parent['standort'] ?? null);
            $info[$ou->id] = [
                'type' => $type,
                'standort' => $standortId,
                'room' => $type === 'raum' ? $ou->id : (in_array($type, ['gruppe', 'other'], true) ? ($parent['room'] ?? null) : null),
                'group' => $type === 'gruppe' ? $label : ($parent['group'] ?? null),
            ];

            if ($ou->type !== $type || $ou->label !== $label || $ou->standort_ou_id !== $standortId) {
                $ou->forceFill(['type' => $type, 'label' => $label, 'standort_ou_id' => $standortId])->saveQuietly();
            }
        }

        return $info;
    }

    /** Create or update by objectGUID, restoring soft-deleted rows. */
    private function upsert(string $model, ?string $guid, array $values)
    {
        $row = $model::withTrashed()->firstOrNew(['guid' => $guid]);
        $exists = $row->exists;
        $wasTrashed = $exists && $row->trashed();
        $row->fill($values);
        if ($wasTrashed) {
            $row->deleted_at = null;
            $this->restored++;
        } elseif (! $exists) {
            $this->created++;
        } elseif ($row->isDirty(array_diff(array_keys($values), ['synced_at']))) {
            $this->updated++;
        }
        $row->save();

        return $row;
    }

    private function dryRunReport($ous, $computers): void
    {
        $known = AdOu::withTrashed()->pluck('guid')->flip();
        $knownPc = AdComputer::withTrashed()->pluck('guid')->flip();
        $newOus = $ous->filter(fn ($o) => ! $known->has($o->getConvertedGuid()))->count();
        $newPcs = $computers->filter(fn ($c) => ! $knownPc->has($c->getConvertedGuid()))->count();
        $this->table(['', 'im AD', 'davon neu'], [
            ['OUs / Räume', $ous->count(), $newOus],
            ['Computer', $computers->count(), $newPcs],
        ]);
        $this->line('Beispiele OUs: '.$ous->take(5)->map(fn ($o) => $o->getDn())->join(' | '));
        $this->line('Beispiele Computer: '.$computers->take(5)->map(fn ($c) => $c->getFirstAttribute('cn'))->join(', '));
        $this->comment('Dry run - nichts geschrieben.');
    }

    private function norm(?string $dn): string
    {
        return implode(',', $this->parts((string) $dn));
    }

    private function parts(string $dn): array
    {
        return array_values(array_filter(array_map(fn ($p) => mb_strtolower(trim($p)), preg_split('/(?<!\\\\),/', $dn))));
    }

    private function parentDn(string $dn): string
    {
        $parts = preg_split('/(?<!\\\\),/', $dn);
        array_shift($parts);

        return implode(',', array_map('trim', $parts));
    }

    private function date($value): ?\Carbon\Carbon
    {
        if ($value instanceof \DateTimeInterface) {
            return \Carbon\Carbon::instance($value);
        }
        if (is_numeric($value) && $value > 0) {        // Windows file time
            return \Carbon\Carbon::createFromTimestamp((int) ($value / 10000000 - 11644473600));
        }
        if (is_string($value) && preg_match('/^(\d{14})/', $value, $m)) { // generalized time (UTC)
            return \Carbon\Carbon::createFromFormat('YmdHis', $m[1], 'UTC');
        }

        return null;
    }
}
