<?php

namespace App\Support;

use App\AdOu;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * OUs that exist only in the app, never in AD (source 'app'): addresses and
 * rooms added on Einstellungen > Standorte & Adressen or /ad-inventory, e.g.
 * a hallway ("Flur") for printers, a kitchen, a new address. They sit in
 * ad_ous next to the imported OUs so every picker has one list; the import
 * never touches them, and nothing is written to AD.
 */
class AppOus
{
    /** a Standort's addresses sit in its "Computer" container when it has one */
    public static function addressParent(AdOu $standort): AdOu
    {
        return AdOu::where('parent_id', $standort->id)->where('type', 'container')->orderBy('id')->first() ?? $standort;
    }

    /** $type 'adresse' (under a Standort / container) or 'raum' (under an Adresse / Etage) */
    public static function create(AdOu $parent, string $type, string $name, ?string $description = null): AdOu
    {
        $name = trim($name);
        self::assertUnique($parent, $type, $name);

        $ou = new AdOu;
        $ou->forceFill([
            // guid column is 36 chars: "app-" + 32 hex
            'guid' => 'app-'.str_replace('-', '', (string) Str::uuid()),
            'source' => 'app',
            'parent_id' => $parent->id,
            'standort_ou_id' => $parent->type === 'standort' ? $parent->id : $parent->standort_ou_id,
            'name' => $name,
            'label' => $name,
            'type' => $type,
            'dn' => 'app:'.$name.'|'.$parent->dn,
            'depth' => $parent->depth + 1,
            'description' => $description,
            'created_by' => auth()->id(),
        ])->save();
        AdLocation::flush();

        return $ou;
    }

    public static function rename(AdOu $ou, string $name, ?string $description = null, ?AdOu $parent = null): AdOu
    {
        $name = trim($name);
        $parent ??= AdOu::findOrFail($ou->parent_id);
        self::assertUnique($parent, $ou->type, $name, $ou->id);

        $ou->forceFill([
            'parent_id' => $parent->id,
            'standort_ou_id' => $parent->type === 'standort' ? $parent->id : $parent->standort_ou_id,
            'name' => $name,
            'label' => $name,
            'dn' => 'app:'.$name.'|'.$parent->dn,
            'depth' => $parent->depth + 1,
            'description' => $description,
        ])->save();
        AdLocation::flush();

        return $ou;
    }

    /** soft delete, incl. app OUs below it (old tickets keep showing the name) */
    public static function delete(AdOu $ou): void
    {
        foreach (AdLocation::descendantIds($ou->id) as $id) {
            AdOu::where('id', $id)->where('source', 'app')->delete();
        }
        AdLocation::flush();
    }

    /** no two addresses / rooms with the same name in the same place */
    public static function assertUnique(AdOu $parent, string $type, string $name, ?int $ignoreId = null): void
    {
        $exists = AdOu::where('parent_id', $parent->id)->where('type', $type)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->get(['label', 'name'])
            ->contains(fn ($o) => mb_strtolower($o->label ?: $o->name) === mb_strtolower($name));
        if ($exists) {
            throw ValidationException::withMessages([
                'name' => $type === 'adresse' ? 'Diese Adresse gibt es hier schon.' : 'Diesen Raum gibt es hier schon.',
            ]);
        }
    }
}
