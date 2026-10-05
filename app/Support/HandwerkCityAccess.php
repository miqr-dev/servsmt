<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Who may open which Handwerk city list (/handwerker/{city}) and its ToDos.
 *
 * - Super_Admin, handwerk_admin: every city.
 * - handwerk: their own city (users.ort) + the cities set manually under
 *   Rollen & Berechtigungen > Handwerk ("Sieht Standorte", view_cities).
 * - anyone else with "Sieht Standorte": those cities.
 * (Other roles are already blocked by config/route_access.php where needed.)
 *
 * Used for: city list, opening / restoring a ticket (TicketAccess), Erledigt,
 * ticket PDF and city PDF.
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

    /** City ToDos: only handwerk_admin / Super_Admin (not the handwerk role). */
    public static function canUseTodos($user = null): bool
    {
        return self::allCities($user);
    }

    public static function authorizeTodos(): void
    {
        abort_unless(self::canUseTodos(), 403, 'Keine Berechtigung für die ToDos.');
    }

    /**
     * Cities this user may see / work on; null = all cities.
     *
     * @return string[]|null
     */
    public static function cities($user = null): ?array
    {
        $user = $user ?: Auth::user();
        if (! $user) {
            return [];
        }
        if (self::allCities($user)) {
            return null;
        }

        $cities = HandwerkResponsibility::viewCities($user);
        if ($user->hasRole('handwerk') && $user->ort) {
            array_unshift($cities, $user->ort);
        }

        return collect($cities)->map(fn ($c) => trim((string) $c))->filter()
            ->unique(fn ($c) => self::slug($c))->values()->all();
    }

    public static function allows(?string $city, $user = null): bool
    {
        $user = $user ?: Auth::user();
        if (! $user) {
            return false;
        }
        $cities = self::cities($user);
        if ($cities === null) {
            return true;
        }
        $slug = self::slug($city);

        return $slug !== '' && collect($cities)->contains(fn ($c) => self::slug($c) === $slug);
    }

    public static function authorize(?string $city): void
    {
        abort_unless(self::allows($city), 403, 'Keine Berechtigung für diese Stadt.');
    }
}
