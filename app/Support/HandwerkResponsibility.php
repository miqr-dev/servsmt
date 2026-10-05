<?php

namespace App\Support;

use App\Handwerk;
use App\HandwerkUserSetting;
use App\User;
use Illuminate\Support\Collection;

/**
 * Handwerk-Zuständigkeiten (2026-10-02), managed by Super_Admin under
 * Rollen & Berechtigungen > Handwerk (/handwerk-zustaendigkeiten).
 * Replaces the hardcoded user ids in the Handwerk code:
 *
 * - Role "Handwerk_verwaltung": gets the notifications that went to user 327
 *   (new ticket, restored, comment on an unassigned ticket, Erledigt by
 *   Sekretariat).
 * - view_cities:   Handwerk tickets of these cities in Meine Tickets (+ may
 *                  open them).
 * - assign_cities: listed in "Zuweisen" on tickets of these cities.
 * - pdf_by_mail:   the assignment e-mail has the ticket PDF attached.
 *
 * Cities are compared case-insensitively (HandwerkCityAccess::slug).
 */
class HandwerkResponsibility
{
    public const VERWALTUNG_ROLE = 'Handwerk_verwaltung';

    /** @var array<int, HandwerkUserSetting|null> per-request cache */
    private static array $settings = [];

    /** Users with the Handwerk_verwaltung role (really assigned), optionally without the acting user. */
    public static function verwaltungUsers($except = null): Collection
    {
        $exceptId = is_object($except) ? $except->id : $except;

        return User::role(self::VERWALTUNG_ROLE)->get()
            ->when($exceptId, fn ($c) => $c->where('id', '!=', (int) $exceptId))
            ->values();
    }

    public static function settingFor($user): ?HandwerkUserSetting
    {
        $id = is_object($user) ? $user->id : (int) $user;
        if (! $id) {
            return null;
        }
        if (! array_key_exists($id, self::$settings)) {
            self::$settings[$id] = HandwerkUserSetting::where('user_id', $id)->first();
        }

        return self::$settings[$id];
    }

    /** @return string[] */
    public static function viewCities($user): array
    {
        return array_values(self::settingFor($user)?->view_cities ?? []);
    }

    public static function canView($user, ?string $city): bool
    {
        $slug = HandwerkCityAccess::slug($city);

        return $slug !== '' && collect(self::viewCities($user))->contains(fn ($c) => HandwerkCityAccess::slug($c) === $slug);
    }

    public static function pdfByMail($user): bool
    {
        return (bool) self::settingFor($user)?->pdf_by_mail;
    }

    /** Users listed in "Zuweisen" for tickets of this city (besides handwerk_admin). */
    public static function assigneesFor(?string $city): Collection
    {
        $slug = HandwerkCityAccess::slug($city);
        if ($slug === '') {
            return collect();
        }
        $ids = HandwerkUserSetting::whereNotNull('assign_cities')->get()
            ->filter(fn ($s) => collect($s->assign_cities)->contains(fn ($c) => HandwerkCityAccess::slug($c) === $slug))
            ->pluck('user_id');

        return $ids->isEmpty() ? collect() : User::whereIn('id', $ids)->get();
    }

    /** City names for the pickers: from the Handwerk tickets + already configured ones. */
    public static function knownCities(): array
    {
        $cities = Handwerk::withTrashed()->whereNotNull('submitter_standort')->distinct()->pluck('submitter_standort');
        foreach (HandwerkUserSetting::all() as $s) {
            $cities = $cities->merge($s->view_cities ?? [])->merge($s->assign_cities ?? []);
        }

        return $cities->map(fn ($c) => trim((string) $c))->filter()
            ->unique(fn ($c) => HandwerkCityAccess::slug($c))
            ->sort(fn ($a, $b) => strcoll($a, $b))->values()->all();
    }

    public static function forget(): void
    {
        self::$settings = [];
    }
}
