<?php

namespace App\Support;

use App\AppSetting;

/**
 * Switches for the Printmarketing order form (2026-10-30), kept in
 * app_settings and changed on Printmarketing Verwaltung (role Printmarketing).
 *
 * Versandtaschen (mit / ohne Fenster, C4 / C5 / DL): hidden and not orderable
 * while off - the items stay defined (resources/js/lib/printmarketingItems.ts)
 * so they can simply be switched on again. Off until someone switches them on.
 */
class PrintmarketingSettings
{
    public const VERSANDTASCHEN = 'printmarketing.versandtaschen_active';

    public const VERSANDTASCHEN_KEYS = [
        'Versandtasche_Fenster_C4', 'Versandtasche_Fenster_C5', 'Versandtasche_Fenster_DL',
        'Versandtasche_ohne_Fenster_C4', 'Versandtasche_ohne_Fenster_C5', 'Versandtasche_ohne_Fenster_DL',
    ];

    public static function versandtaschenActive(): bool
    {
        try {
            return (string) AppSetting::where('key', self::VERSANDTASCHEN)->value('value') === '1';
        } catch (\Throwable $e) {
            report($e); // e.g. table app_settings missing (migrate not run)

            return false;
        }
    }

    public static function setVersandtaschen(bool $active, ?int $userId): void
    {
        $setting = AppSetting::firstOrNew(['key' => self::VERSANDTASCHEN]);
        $setting->value = $active ? '1' : '0';
        $setting->updated_by = $userId;
        $setting->save();
    }

    /** item keys the order form must not accept right now */
    public static function blockedItems(): array
    {
        return self::versandtaschenActive() ? [] : self::VERSANDTASCHEN_KEYS;
    }
}
