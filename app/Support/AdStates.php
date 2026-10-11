<?php

namespace App\Support;

use App\AdOu;

/** Bundesland of an AD Standort (ad_ous.state, else config ad_inventory.states). */
class AdStates
{
    public const OPTIONS = [
        'Baden-Württemberg', 'Bayern', 'Berlin', 'Brandenburg', 'Bremen', 'Hamburg', 'Hessen',
        'Mecklenburg-Vorpommern', 'Niedersachsen', 'Nordrhein-Westfalen', 'Rheinland-Pfalz',
        'Saarland', 'Sachsen', 'Sachsen-Anhalt', 'Schleswig-Holstein', 'Thüringen',
    ];

    public static function default(?string $standortLabel): ?string
    {
        $key = self::norm($standortLabel);
        foreach (config('ad_inventory.states', []) as $state => $cities) {
            foreach ((array) $cities as $city) {
                if (self::norm($city) === $key && $key !== '') {
                    return $state;
                }
            }
        }

        return null;
    }

    public static function of(AdOu $standort): ?string
    {
        return $standort->state ?: self::default($standort->label ?: $standort->name);
    }

    /** "Döbeln" ~ "doebeln" */
    public static function norm(?string $v): string
    {
        return strtr(mb_strtolower(trim((string) $v)), ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss']);
    }
}
