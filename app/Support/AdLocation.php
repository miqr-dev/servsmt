<?php

namespace App\Support;

use App\AdComputer;
use App\AdOu;

/**
 * Display data for AD rooms / computers on ticket pages (AD switch step 3,
 * 2026-10-09). All OUs are loaded once per request (a few hundred rows) and
 * cached here, so lists can call this per row without extra queries.
 * Deleted OUs / computers are included (withTrashed): an old ticket keeps
 * showing where it was, marked "nicht mehr im AD".
 *
 * room(): ['id', 'type', 'standort', 'adresse', 'etage', 'raum', 'label', 'path', 'deleted', 'app']
 *   label = "3.05 (3.OG)", path = "Dresden › Löscherstr. -16 (DD1) › 3.OG › 3.05"
 *   Since step 4 a Handwerk ticket can point to an Adresse or Etage (no room
 *   picked): then raum is null and label is the Etage ("3.OG") or "".
 */
class AdLocation
{
    private static ?array $ous = null;

    private static function ous(): array
    {
        if (self::$ous === null) {
            self::$ous = AdOu::withTrashed()
                ->get(['id', 'parent_id', 'type', 'label', 'name', 'source', 'deleted_at'])
                ->keyBy('id')->all();
        }

        return self::$ous;
    }

    /** forget the cached OUs (after adding / changing OUs in the same request) */
    public static function flush(): void
    {
        self::$ous = null;
    }

    /** AD room (or any OU: walks up to collect Standort / Adresse / Etage). */
    public static function room(?int $ouId): ?array
    {
        if (! $ouId || ! isset(self::ous()[$ouId])) {
            return null;
        }
        $ous = self::ous();
        $start = $ous[$ouId];
        $parts = ['standort' => null, 'adresse' => null, 'etage' => null, 'raum' => null];
        $path = [];
        $cur = $start;
        $guard = 0;
        while ($cur && $guard++ < 30) {
            $label = $cur->label ?: $cur->name;
            if (array_key_exists($cur->type, $parts) && $parts[$cur->type] === null) {
                $parts[$cur->type] = $label;
            }
            if (! in_array($cur->type, ['container', 'gruppe'], true)) {
                array_unshift($path, $label);
            }
            $cur = $cur->parent_id ? ($ous[$cur->parent_id] ?? null) : null;
        }
        $isRoom = $start->type === 'raum';
        $raum = $isRoom ? ($start->label ?: $start->name) : null;
        $label = $isRoom
            ? ($parts['etage'] ? "{$raum} ({$parts['etage']})" : $raum)
            : ($start->type === 'etage' ? (string) $parts['etage'] : '');

        return [
            'id' => $start->id,
            'type' => $start->type,
            'standort' => $parts['standort'],
            'adresse' => $parts['adresse'],
            'etage' => $parts['etage'],
            'raum' => $raum,
            'label' => $label,
            'path' => implode(' › ', $path),
            'deleted' => $start->deleted_at !== null,
            'app' => $start->source === 'app', // room added in the app, not in AD
        ];
    }

    /** ids of the Standort / Adresse / Etage / Raum the OU is in (incl. the OU itself) */
    public static function ancestry(?int $ouId): array
    {
        $out = ['standort' => null, 'adresse' => null, 'etage' => null, 'raum' => null];
        $ous = self::ous();
        $cur = $ouId ? ($ous[$ouId] ?? null) : null;
        $guard = 0;
        while ($cur && $guard++ < 30) {
            if (array_key_exists($cur->type, $out) && $out[$cur->type] === null) {
                $out[$cur->type] = $cur->id;
            }
            $cur = $cur->parent_id ? ($ous[$cur->parent_id] ?? null) : null;
        }

        return $out;
    }

    /** the OU and every OU below it */
    public static function descendantIds(int $ouId): array
    {
        $children = [];
        foreach (self::ous() as $ou) {
            if ($ou->parent_id) {
                $children[$ou->parent_id][] = $ou->id;
            }
        }
        $ids = [$ouId];
        for ($i = 0; $i < count($ids) && $i < 5000; $i++) {
            foreach ($children[$ids[$i]] ?? [] as $child) {
                $ids[] = $child;
            }
        }

        return $ids;
    }

    /** device added in the app (ad_devices), with its room */
    public static function device(?int $deviceId): ?array
    {
        if (! $deviceId) {
            return null;
        }
        $d = \App\AdDevice::withTrashed()->find($deviceId, ['id', 'ad_ou_id', 'kind', 'name', 'invnr', 'checked_at', 'deleted_at']);
        if (! $d) {
            return null;
        }

        return [
            'id' => $d->id,
            'name' => $d->name,
            'kind' => $d->kind,
            'kind_label' => AdDevices::KINDS[$d->kind] ?? 'Gerät',
            'invnr' => $d->invnr,
            'room' => self::room($d->ad_ou_id),
            'checked' => $d->checked_at !== null,
            'deleted' => $d->deleted_at !== null,
        ];
    }

    public static function computer(?int $computerId): ?array
    {
        if (! $computerId) {
            return null;
        }
        $pc = AdComputer::withTrashed()->find($computerId, ['id', 'name', 'ad_ou_id', 'room_ou_id', 'group', 'deleted_at']);
        if (! $pc) {
            return null;
        }

        return [
            'id' => $pc->id,
            'name' => $pc->name,
            'group' => $pc->group,
            'room' => self::room($pc->room_ou_id ?: $pc->ad_ou_id),
            'deleted' => $pc->deleted_at !== null,
        ];
    }
}
