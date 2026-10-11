<?php

namespace App\Support;

use App\AdDevice;
use App\AdOu;

/**
 * Devices added to AD / app rooms in the app (2026-10-09): by users in the
 * ticket forms when the device is not in the room's list, or by IT on
 * Einstellungen > Standorte & Adressen. AD is never written.
 */
class AdDevices
{
    public const KINDS = [
        'rechner' => 'Rechner',
        'drucker' => 'Drucker',
        'scanner' => 'Scanner',
        'telefon' => 'Telefon',
        'beamer' => 'Beamer',
        'sonstiges' => 'Sonstiges',
    ];

    /** device list of a form: old-inventory gart ids + app device kinds */
    public const TYPES = [
        'printer' => ['garts' => [5], 'kinds' => ['drucker', 'scanner']],
        'phone' => ['garts' => [15], 'kinds' => ['telefon']],
        'projector' => ['garts' => [13], 'kinds' => ['beamer']],
    ];

    /**
     * Adds a device to a room. The same name of the same kind in the same room
     * is reused, so a double click or two users never create it twice.
     * IT admins' entries count as checked right away.
     */
    public static function create(?AdOu $room, string $kind, string $name, ?string $invnr = null, ?string $note = null): AdDevice
    {
        $name = trim($name);
        // $room null: a PC added in a software form (no room asked there)
        $existing = AdDevice::where('ad_ou_id', $room?->id)->where('kind', $kind)->get()
            ->first(fn ($d) => mb_strtolower($d->name) === mb_strtolower($name));
        if ($existing) {
            return $existing;
        }

        $user = auth()->user();
        $isIt = $user && $user->hasAnyRole(['Super_Admin', 'admin']);

        return AdDevice::create([
            'ad_ou_id' => $room?->id,
            'kind' => $kind,
            'name' => $name,
            'invnr' => $invnr ? trim($invnr) : null,
            'note' => $note,
            'created_by' => $user?->id,
            'checked_at' => $isIt ? now() : null,
            'checked_by' => $isIt ? $user->id : null,
        ]);
    }

    /** JSON for the pickers */
    public static function payload(AdDevice $d): array
    {
        return [
            'id' => $d->id,
            'name' => $d->name,
            'kind' => $d->kind,
            'invnr' => $d->invnr,
            'room_id' => $d->ad_ou_id,
            'checked' => $d->checked_at !== null,
        ];
    }
}
