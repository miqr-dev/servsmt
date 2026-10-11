<?php

namespace App\Support;

use App\AppSetting;
use Carbon\Carbon;

/**
 * Default Windows password for new employees (2026-10-11).
 *
 * Sent in the "Neuer Mitarbeiter" mail (TicketNotification, Erledigt 2).
 * Was hardcoded; now Einstellungen > Neuer Mitarbeiter
 * (/settings/neuer-mitarbeiter, Super_Admin).
 *
 * It changes every year on 1 January (the year in it goes up, e.g. 2026 ->
 * 2027, together with the default password in Active Directory). Reminder:
 * from 1 January, as long as it wasn't saved this year,
 *  - a box on the Dashboard of Super_Admins,
 *  - a warning on "Neuer Mitarbeiter" tickets next to Erledigt 2,
 *  - a mail to all Super_Admins on 1-7 January
 *    (servsmt:employee-password-reminder, app/Console/Kernel.php).
 * Saving the same value again also counts as "checked this year".
 */
class EmployeePassword
{
    public const KEY = 'new_employee_password';

    /** @var AppSetting|null|false per-request cache (false = not loaded) */
    private static $setting = false;

    public static function setting(): ?AppSetting
    {
        if (self::$setting === false) {
            try {
                self::$setting = AppSetting::where('key', self::KEY)->first();
            } catch (\Throwable $e) {
                report($e); // e.g. "php artisan migrate" not run yet (table app_settings)
                self::$setting = null;
            }
        }

        return self::$setting;
    }

    public static function value(): string
    {
        return (string) (self::setting()?->value ?? '');
    }

    public static function changedAt(): ?Carbon
    {
        return self::setting()?->updated_at;
    }

    /** Set, but not saved since 1 January of this year. */
    public static function isOutdated(): bool
    {
        $changed = self::changedAt();

        return self::value() !== '' && $changed !== null && $changed->year < Carbon::now()->year;
    }

    /** The same password with this year in it ("…2026…" -> "…2027…"); null if it has no year or is current. */
    public static function suggestion(): ?string
    {
        $value = self::value();
        if (! preg_match('/20\d{2}/', $value)) {
            return null;
        }
        $new = preg_replace('/20\d{2}/', (string) Carbon::now()->year, $value, 1);

        return $new !== $value ? $new : null;
    }

    public static function set(string $value, ?int $userId): void
    {
        $setting = AppSetting::firstOrNew(['key' => self::KEY]);
        $setting->value = $value;
        $setting->updated_by = $userId;
        $setting->updated_at = Carbon::now(); // also when unchanged = checked this year
        $setting->save();
        self::$setting = false;
    }

    /** Dashboard / ticket page: null when nothing is due. */
    public static function reminder(): ?array
    {
        if (! self::isOutdated()) {
            return null;
        }

        return [
            'year' => Carbon::now()->year,
            'changedAt' => self::changedAt()?->toDateString(),
        ];
    }
}
