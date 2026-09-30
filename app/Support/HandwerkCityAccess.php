<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Who may open which Handwerk city list (/handwerker/{city}) and its ToDos.
 *
 * - Super_Admin, handwerk_admin: every city.
 * - handwerk: only their own city (users.ort).
 * (Other roles are already blocked by config/route_access.php.)
 *
 * URLs use a lowercase slug ("erfurt", "doebeln" for Döbeln), so both sides
 * are normalised before comparing.
 */
class HandwerkCityAccess
{
    public static function slug(?string $city): string
    {
        $c = mb_strtolower(trim((string) $city), 'UTF-8');

        return str_replace(['döbeln', 'ö', 'ä', 'ü', 'ß'], ['doebeln', 'oe', 'ae', 'ue', 'ss'], $c);
    }

    public static function allCities($user = null): bool
    {
        $user = $user ?: Auth::user();

        return $user && $user->hasAnyRole(['Super_Admin', 'handwerk_admin']);
    }

    public static function allows(?string $city, $user = null): bool
    {
        $user = $user ?: Auth::user();
        if (! $user) {
            return false;
        }
        if (self::allCities($user)) {
            return true;
        }

        return $user->hasRole('handwerk') && $user->ort && self::slug($user->ort) === self::slug($city);
    }

    public static function authorize(?string $city): void
    {
        abort_unless(self::allows($city), 403, 'Keine Berechtigung für diese Stadt.');
    }
}
