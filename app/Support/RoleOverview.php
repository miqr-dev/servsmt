<?php

namespace App\Support;

use App\Http\Middleware\EnforceRouteAccess;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Rollen & Berechtigungen > Übersicht (/rollen-uebersicht, Super_Admin only,
 * 2026-10-11): every role and what it can do, for Roles/Overview.vue.
 *
 * - Rights that come from config/route_access.php are resolved LIVE through
 *   EnforceRouteAccess::rolesFor() with a sample route ('route' => [METHOD,
 *   URI as Laravel defines it]), so the page always shows what the
 *   middleware enforces. Change the table and the page changes with it.
 *   A sample route that no longer exists is flagged on the page.
 * - Rights checked in controllers / pages (own city, owner, strict role
 *   checks, notifications) are listed here by hand ('roles').
 *   When such a check changes in the code, update it here too.
 * - Members come from model_has_roles (users not soft-deleted).
 *
 * Super_Admin counts as every role (App\User::hasRole), so it gets every
 * right automatically - except 'strict' rows (notifications, "Zuweisen"
 * lists, hasAssignedRole checks): those only reach users who really have
 * the role.
 */
class RoleOverview
{
    public const GROUPS = [
        'system' => 'System',
        'basis' => 'Basis',
        'it' => 'IT',
        'standort' => 'Standort',
        'handwerk' => 'Handwerk',
        'korso' => 'Korso',
        'personal' => 'Personal',
        'inventar' => 'Inventar',
        'uebungsfirmen' => 'Übungsfirmen',
    ];

    public const AREAS = [
        'allgemein' => 'Allgemein',
        'it' => 'IT-Tickets',
        'handwerk' => 'Handwerk',
        'korso' => 'Korso',
        'personal' => 'Personal & E-Mail',
        'teilnehmer' => 'Teilnehmer',
        'inventar' => 'Inventar & AD',
        'uebungsfirmen' => 'Übungsfirmen',
        'admin' => 'Administration',
        'mail' => 'Benachrichtigungen & Zuweisen',
    ];

    /**
     * Every role the code checks, in page order.
     * kind: all = Super_Admin, base = Verwaltung, rights = adds rights,
     *       notify = only notifications, no rights.
     * queried: the code loads the members with User::role(...). Spatie
     *       throws if the role is missing in the DB, so that part breaks.
     */
    public const ROLES = [
        'Super_Admin' => [
            'group' => 'system',
            'kind' => 'all',
            'summary' => 'Darf alles. Zählt im Code automatisch als jede Rolle.',
            'notes' => [
                'Hat die Rechte jeder anderen Rolle, ohne sie zugewiesen zu bekommen.',
                'Alle Super_Admins sind die IT: sie stehen in „Zuweisen“ der IT-Tickets und bekommen die IT-Benachrichtigungen.',
                'Benachrichtigungen und „Zuweisen“-Listen von Handwerk und Korso bekommt ein Super_Admin nur, wenn ihm die Rolle dort wirklich zugewiesen ist.',
            ],
            'assign' => 'Benutzer › Bearbeiten',
            'menu' => ['Einstellungen', 'Inventar', 'Ticket (Offen, Erledigt, Meine Tickets)', 'Korso Dashboard', 'Handwerkaufgaben', 'Übungsfirmen', 'Rollen & Berechtigungen', 'Teilnehmer Liste', 'Mitarbeiter Liste', 'Kündigungen', 'Lizenzen', 'E-Mail-Weiterleitungen', 'AD Räume & Computer'],
            'queried' => 'IT-Benachrichtigungen und „Zuweisen“ der IT-Tickets',
        ],
        'Verwaltung' => [
            'group' => 'basis',
            'kind' => 'base',
            'summary' => 'Basisrolle jedes Mitarbeiters: Tickets erstellen (IT, Handwerk, Korso) und die eigenen Tickets verfolgen.',
            'notes' => [
                'Wird beim ersten Windows-Login automatisch vergeben.',
                'Die Zusatzrollen kommen oben drauf, sie enthalten die Rechte von Verwaltung nicht selbst. Wer Verwaltung verliert, kann keine Tickets mehr erstellen.',
            ],
            'assign' => 'automatisch beim ersten Login (sonst Benutzer › Bearbeiten)',
            'menu' => ['Dashboard', 'Meine Tickets', 'IT Ticket', 'Korso Ticket', 'Handwerk Ticket'],
        ],
        'admin' => [
            'group' => 'it',
            'kind' => 'rights',
            'summary' => 'IT-Admin ohne Super_Admin: IT-Ticketlisten und alle Aktionen auf IT-Tickets, AD Räume & Computer.',
            'notes' => [
                'Steht nicht in „Zuweisen“ der IT-Tickets und bekommt keine IT-Benachrichtigungen (die gehen nur an Super_Admin).',
                'Nicht im Seeder. Hat niemand die Rolle, kann sie aus dem Code entfernt werden (IT-Admin = Super_Admin).',
            ],
            'assign' => 'Benutzer › Bearbeiten',
            'menu' => ['Ticket (Offen, Erledigt, Meine Tickets)', 'Handwerkaufgaben', 'AD Räume & Computer'],
        ],
        'Sekretariat' => [
            'group' => 'standort',
            'kind' => 'rights',
            'summary' => 'Sekretariat eines Standorts: Teilnehmer Liste, E-Mail-Weiterleitungen und Handwerk-Aufgaben des eigenen Standorts.',
            'notes' => [
                'Eigener Standort = Ort im Benutzerprofil (Berlin: zusätzlich die Straße).',
                'Korso-Tickets „als Sekretariat“ kommen nicht von dieser Rolle, sondern von der Mitgliedschaft in einer Sekretariat-Gruppe (Korso Dashboard › Sek Gruppen).',
            ],
            'assign' => 'Benutzer › Bearbeiten',
            'menu' => ['Teilnehmer Liste'],
        ],
        'Teilnehmer_Info' => [
            'group' => 'standort',
            'kind' => 'rights',
            'summary' => 'Nur die Teilnehmer Liste des eigenen Standorts.',
            'notes' => ['Eigener Standort = Ort im Benutzerprofil (Berlin: nach Straße getrennt).'],
            'assign' => 'Benutzer › Bearbeiten',
            'menu' => ['Teilnehmer Liste'],
        ],
        'handwerk_admin' => [
            'group' => 'handwerk',
            'kind' => 'rights',
            'summary' => 'Leitung Handwerk: alle Städte, Zuweisen, Admin-Notizen, ToDos, Tickets für andere Städte erstellen.',
            'notes' => ['Steht in „Zuweisen“ aller Handwerk-Tickets.'],
            'assign' => 'Benutzer › Bearbeiten',
            'menu' => ['Handwerk Ticket (mit allen Stadt-Kacheln)'],
            'queried' => '„Zuweisen“ auf der Handwerk-Ticketseite',
        ],
        'handwerk' => [
            'group' => 'handwerk',
            'kind' => 'rights',
            'summary' => 'Handwerker: Tickets der eigenen Stadt sehen, als PDF holen und als erledigt markieren.',
            'notes' => [
                'Eigene Stadt = Ort im Benutzerprofil. Weitere Städte: Rollen & Berechtigungen › Handwerk › „Sieht Standorte“.',
                'Keine ToDos, kein Zuweisen.',
                'Nicht im Seeder.',
            ],
            'assign' => 'Benutzer › Bearbeiten',
            'menu' => ['Handwerk Ticket (Kachel der eigenen Stadt)'],
        ],
        'Handwerk_verwaltung' => [
            'group' => 'handwerk',
            'kind' => 'notify',
            'summary' => 'Nur Benachrichtigungen zu Handwerk-Tickets, keine zusätzlichen Rechte.',
            'notes' => [],
            'assign' => 'Rollen & Berechtigungen › Handwerk',
            'menu' => [],
            'queried' => 'neues Handwerk-Ticket, Wiederherstellen, Kommentare, Erledigt',
        ],
        'Korso_Admin' => [
            'group' => 'korso',
            'kind' => 'rights',
            'summary' => 'Leitung Korso: Korso Dashboard mit Filter je Mitarbeiter, Optionen, Rechtevergabe, Sek-Gruppen, endgültig löschen.',
            'notes' => [
                'Steht nur in „Zuweisen“ und darf nur Anhänge hochladen / löschen, wenn zusätzlich Korso_ma.',
                'Rechtevergabe = Korso_ma vergeben oder entziehen.',
            ],
            'assign' => 'Rollen & Berechtigungen › Korso',
            'menu' => ['Korso Dashboard'],
        ],
        'Korso_ma' => [
            'group' => 'korso',
            'kind' => 'rights',
            'summary' => 'Korso-Mitarbeiter: Korso Dashboard, Tickets bearbeiten, zuweisen, erledigen; steht in „Zuweisen“.',
            'notes' => [],
            'assign' => 'Korso Dashboard › Rechtevergabe (Korso_Admin) oder Benutzer › Bearbeiten',
            'menu' => ['Korso Dashboard'],
            'queried' => 'Korso Dashboard, neue Korso-Tickets, Kommentare',
        ],
        'Korso_verwaltung' => [
            'group' => 'korso',
            'kind' => 'notify',
            'summary' => 'Nur Benachrichtigung, wenn ein nicht zugewiesenes Korso-Ticket wiederhergestellt wird.',
            'notes' => [],
            'assign' => 'Rollen & Berechtigungen › Korso',
            'menu' => [],
            'queried' => 'Korso-Ticket wiederherstellen',
        ],
        'Printmarketing' => [
            'group' => 'korso',
            'kind' => 'rights',
            'summary' => 'Printmarketing Verwaltung: bestellte Artikel aller offenen Printmarketing-Tickets, PDF-Export.',
            'notes' => ['Der Link steht im Korso Dashboard. Ohne Korso_ma / Korso_Admin nur direkt über /printmarketing-management erreichbar.'],
            'assign' => 'Rollen & Berechtigungen › Korso',
            'menu' => [],
            'queried' => 'Rollen & Berechtigungen › Korso',
        ],
        'Onlinemarketing' => [
            'group' => 'korso',
            'kind' => 'notify',
            'summary' => 'Nur Benachrichtigung bei jedem neuen Onlinemarketing-Ticket (jede Priorität).',
            'notes' => [],
            'assign' => 'Rollen & Berechtigungen › Korso',
            'menu' => [],
            'queried' => 'neues Onlinemarketing-Ticket',
        ],
        'HR' => [
            'group' => 'personal',
            'kind' => 'rights',
            'summary' => 'Personal: Mitarbeiter Liste und Kündigungen.',
            'notes' => [
                'Darf E-Mail-Weiterleitungen nicht als entfernt markieren (Entscheidung 2026-10-09).',
                'Lizenzen und die Übersicht aller E-Mail-Weiterleitungen sind nur für Super_Admin.',
            ],
            'assign' => 'Benutzer › Bearbeiten',
            'menu' => ['Mitarbeiter Liste', 'Kündigungen'],
        ],
        'INV' => [
            'group' => 'inventar',
            'kind' => 'rights',
            'summary' => 'Altes Inventar: Geräte ansehen, erfassen, Inventur, Etiketten drucken.',
            'notes' => [
                'Bewegen, Ausmustern, Umbenennen und „Fehlende Inventarnummern“ bleiben Super_Admin vorbehalten (Berechtigungen der Rolle).',
                'Der Inventar-Bereich soll später entfallen (Geräte kommen in die AD-Räume).',
            ],
            'assign' => 'Benutzer › Bearbeiten',
            'menu' => ['Inventar'],
        ],
        'Terminal' => [
            'group' => 'uebungsfirmen',
            'kind' => 'rights',
            'summary' => 'Übungsfirmen: Zugangsdaten je Standort ansehen und kopieren (Windows & E-Mail, Lexware). Anlegen, ändern, löschen und Excel-Export nur Super_Admin.',
            'notes' => [],
            'assign' => 'Benutzer › Bearbeiten',
            'menu' => ['Übungsfirmen'],
        ],
    ];

    /**
     * What can be done, per area.
     *  route:  [METHOD, URI] resolved live against config/route_access.php
     *  roles:  roles checked in code (added to the route's roles);
     *          no route and no roles = Super_Admin only
     *  only:   the code narrows the route's roles to these
     *  scope:  role => limitation ("nur eigene Stadt")
     *  open:   every logged-in user (only while route_access has no rule)
     *  strict: only users that really have the role (Super_Admin not implied)
     *  others: who else, not by role ("Ersteller des Tickets")
     */
    private static function catalog(): array
    {
        $ownCity = 'nur eigene Stadt + „Sieht Standorte“';
        $ownSite = 'nur eigener Standort';

        return [
            // ─── Allgemein ───────────────────────────────────────────────
            ['area' => 'allgemein', 'label' => 'Dashboard: „Neu für dich“, Nachrichtenleiste', 'route' => ['GET', '/'], 'open' => true],
            ['area' => 'allgemein', 'label' => 'Meine Tickets und Erledigte (IT, Handwerk, Korso)', 'route' => ['GET', 'usertickets/{city?}'], 'open' => true],
            ['area' => 'allgemein', 'label' => 'Eigene Tickets öffnen, kommentieren und wiederherstellen', 'open' => true, 'others' => 'als Ersteller des Tickets'],
            ['area' => 'allgemein', 'label' => 'Kommentare: eigene bearbeiten / löschen, auf andere antworten', 'open' => true, 'hint' => 'auf jedem Ticket, das man öffnen darf'],
            ['area' => 'allgemein', 'label' => 'Profil bearbeiten', 'route' => ['GET', 'settings/firstpage/{id}/edit'], 'hint' => 'nur das eigene'],
            ['area' => 'allgemein', 'label' => 'Dashboard: „Ticket erstellen“ und „Mitarbeiter Info“', 'route' => ['GET', 'dashboard/mitarbeiter-suche']],
            ['area' => 'allgemein', 'label' => 'Dashboard: eigene E-Mail-Weiterleitungen', 'roles' => ['Verwaltung'], 'hint' => 'Sekretariat sieht statt dessen die des Standorts'],

            // ─── IT-Tickets ──────────────────────────────────────────────
            ['area' => 'it', 'label' => 'IT-Ticket erstellen (alle Formulare)', 'route' => ['GET', 'ticket.index']],
            ['area' => 'it', 'label' => 'Fehlendes Gerät im Formular hinzufügen (Drucker, Telefon, PC …)', 'route' => ['POST', 'ad-locations/devices'], 'open' => true, 'hint' => 'IT prüft es unter Einstellungen › Standorte & Adressen'],
            ['area' => 'it', 'label' => 'Ticket im Namen eines anderen Mitarbeiters erstellen', 'roles' => []],
            ['area' => 'it', 'label' => 'Ticketlisten: Offen, Nicht zugewiesen, Erledigt, je Admin, je Stadt', 'route' => ['GET', 'opentickets']],
            ['area' => 'it', 'label' => 'Jedes IT-Ticket öffnen und wiederherstellen', 'roles' => ['admin'], 'others' => 'Ersteller: das eigene Ticket'],
            ['area' => 'it', 'label' => 'Zuweisen, Priorität, Status, Admin-Notizen', 'route' => ['POST', 'ticket/assignTo']],
            ['area' => 'it', 'label' => 'Erledigt / Erledigt 2 (Neuer Mitarbeiter)', 'route' => ['POST', 'ticket.delete/{myTicket}']],
            ['area' => 'it', 'label' => 'Neuer Mitarbeiter: Benutzername, Passwort, E-Mail eintragen', 'route' => ['POST', 'ticket/employee_username']],
            ['area' => 'it', 'label' => 'Vor-Ort-Termin setzen', 'route' => ['POST', 'ticket/on_location']],
            ['area' => 'it', 'label' => 'E-Mail-Weiterleitung als entfernt markieren', 'route' => ['POST', 'ticket/{ticketId}/forwarding-removed']],
            ['area' => 'it', 'label' => 'IT-Ticket endgültig löschen', 'route' => ['POST', 'ticket.force_delete/{id}']],
            ['area' => 'it', 'label' => 'Offene Tickets einer Stadt als PDF', 'route' => ['GET', 'city/{city}/tickets/pdf']],

            // ─── Handwerk ────────────────────────────────────────────────
            ['area' => 'handwerk', 'label' => 'Handwerk-Ticket erstellen (alle Formulare), eigene Liste', 'route' => ['GET', 'handwerk']],
            ['area' => 'handwerk', 'label' => 'Handwerk-Ticket für eine andere Stadt erstellen („Standort ändern“)', 'roles' => ['handwerk_admin']],
            ['area' => 'handwerk', 'label' => 'Stadt-Listen (Kacheln auf der Handwerk-Seite)', 'route' => ['GET', 'handwerker/{city}'], 'scope' => ['handwerk' => $ownCity], 'others' => 'Benutzer mit „Sieht Standorte“'],
            ['area' => 'handwerk', 'label' => 'ToDos der Städte', 'route' => ['POST', 'handwerker/{city}/todos'], 'only' => ['handwerk_admin'], 'hint' => 'die Rolle handwerk sieht in der Stadt-Liste nur die Tickets'],
            ['area' => 'handwerk', 'label' => 'Fremde Handwerk-Tickets öffnen und wiederherstellen', 'roles' => ['handwerk_admin', 'Sekretariat', 'handwerk'], 'scope' => ['handwerk' => $ownCity], 'others' => 'Ersteller; Benutzer mit „Sieht Standorte“'],
            ['area' => 'handwerk', 'label' => 'Meine Tickets: Handwerk-Tickets der ganzen Stadt', 'roles' => ['handwerk_admin', 'handwerk', 'Sekretariat'], 'scope' => ['handwerk_admin' => 'eigene Stadt', 'handwerk' => 'eigene Stadt', 'Sekretariat' => 'eigene Stadt', 'Super_Admin' => 'eigene Stadt'], 'others' => 'Benutzer mit „Sieht Standorte“'],
            ['area' => 'handwerk', 'label' => 'Erledigt markieren', 'route' => ['POST', 'handwerk.delete/{myhandwerk}'], 'scope' => ['handwerk' => $ownCity]],
            ['area' => 'handwerk', 'label' => 'Zuweisen und Admin-Notizen', 'route' => ['POST', 'handwerk/assignTo']],
            ['area' => 'handwerk', 'label' => 'Offene Tickets einer Stadt als PDF', 'route' => ['GET', 'handwerk/{city}/open-tickets-pdf'], 'scope' => ['handwerk' => $ownCity]],
            ['area' => 'handwerk', 'label' => 'Einzelnes Handwerk-Ticket als PDF', 'roles' => ['handwerk_admin', 'handwerk'], 'scope' => ['handwerk' => $ownCity], 'others' => 'Benutzer mit „Sieht Standorte“'],
            ['area' => 'handwerk', 'label' => 'Dashboard: offene Handwerk-Aufgaben der eigenen Stadt', 'roles' => ['Sekretariat']],

            // ─── Korso ───────────────────────────────────────────────────
            ['area' => 'korso', 'label' => 'Korso-Ticket erstellen (Printmarketing, Onlinemarketing, Zertifizierung)', 'route' => ['GET', 'korso']],
            ['area' => 'korso', 'label' => 'Korso Dashboard (Eigene, Nicht zugewiesen, Offene, Erledigte)', 'route' => ['GET', 'korso-dashboard']],
            ['area' => 'korso', 'label' => 'Jedes Korso-Ticket öffnen und wiederherstellen', 'roles' => ['Korso_ma', 'Korso_Admin'], 'others' => 'Ersteller; Mitglieder der Sek-Gruppe, wenn „als Sekretariat“ erstellt'],
            ['area' => 'korso', 'label' => 'Zuweisen, Status, Priorität, Erledigt, Löschen, PDF', 'route' => ['POST', 'korso/assign']],
            ['area' => 'korso', 'label' => 'Interne Notizen', 'route' => ['POST', 'korso/comments/store']],
            ['area' => 'korso', 'label' => 'Anhänge hochladen und löschen', 'route' => ['POST', 'korso/{id}/upload-attachment'], 'only' => ['Korso_ma'], 'hint' => 'Korso_Admin nur mit zusätzlich Korso_ma'],
            ['area' => 'korso', 'label' => 'Dashboard-Filter je Korso-Mitarbeiter', 'roles' => ['Korso_Admin']],
            ['area' => 'korso', 'label' => 'Onlinemarketing- und Zertifizierung-Optionen', 'route' => ['GET', 'onlinemarketing_items']],
            ['area' => 'korso', 'label' => 'Rechtevergabe (Korso_ma vergeben / entziehen)', 'route' => ['GET', 'user-management']],
            ['area' => 'korso', 'label' => 'Sekretariat-Gruppen verwalten', 'route' => ['GET', 'sek-groups']],
            ['area' => 'korso', 'label' => 'Korso-Ticket endgültig löschen', 'route' => ['POST', 'korso/force_delete/{id}']],
            ['area' => 'korso', 'label' => 'Printmarketing Verwaltung und PDF-Export', 'route' => ['GET', 'printmarketing-management'], 'roles' => ['Printmarketing']],

            // ─── Personal & E-Mail ───────────────────────────────────────
            ['area' => 'personal', 'label' => 'Mitarbeiter Liste (ansehen, bearbeiten)', 'route' => ['GET', 'employees']],
            ['area' => 'personal', 'label' => 'Kündigungen (anlegen, bearbeiten, löschen)', 'route' => ['GET', 'terminations']],
            ['area' => 'personal', 'label' => 'Dashboard: Kündigungen', 'roles' => ['HR', 'Super_Admin'], 'strict' => true, 'scope' => ['HR' => 'volle Liste', 'Super_Admin' => 'nur fällige (heute oder überfällig)']],
            ['area' => 'personal', 'label' => 'Lizenzen', 'route' => ['GET', 'licenses']],
            ['area' => 'personal', 'label' => 'E-Mail-Weiterleitungen: alle (aktiv und Verlauf)', 'route' => ['GET', 'email-forwardings']],
            ['area' => 'personal', 'label' => 'Dashboard: E-Mail-Weiterleitungen des eigenen Standorts', 'roles' => ['Sekretariat'], 'strict' => true, 'scope' => ['Sekretariat' => $ownSite]],
            ['area' => 'personal', 'label' => 'Dashboard: ablaufende Lizenzen, überfällige Weiterleitungen', 'roles' => []],

            // ─── Teilnehmer ──────────────────────────────────────────────
            ['area' => 'teilnehmer', 'label' => 'Teilnehmer Liste (mit Zugangsdaten) und Export', 'route' => ['GET', 'participants'], 'roles' => ['Teilnehmer_Info', 'Sekretariat'], 'scope' => ['Teilnehmer_Info' => $ownSite, 'Sekretariat' => $ownSite, 'Super_Admin' => 'alle Standorte, mit Filter']],
            ['area' => 'teilnehmer', 'label' => 'Teilnehmer bearbeiten, löschen, Benutzernamen eintragen', 'route' => ['POST', 'participant.username']],

            // ─── Inventar & AD ───────────────────────────────────────────
            ['area' => 'inventar', 'label' => 'Inventar öffnen (Geräte, Suche, Listen)', 'route' => ['GET', 'inventory'], 'roles' => ['INV']],
            ['area' => 'inventar', 'label' => 'Erfassen (Auto / Manuell), Inventur, Etiketten drucken', 'route' => ['POST', 'room_inventur']],
            ['area' => 'inventar', 'label' => 'Bewegen, Ausmustern, Umbenennen, Fehlende Inventarnummern', 'roles' => [], 'hint' => 'Berechtigungen, die INV nicht hat'],
            ['area' => 'inventar', 'label' => 'AD Räume & Computer (ansehen, Zuordnung, „Jetzt aktualisieren“)', 'route' => ['GET', 'ad-inventory']],
            ['area' => 'inventar', 'label' => 'Standorte & Adressen der Ticketformulare (ausblenden, anlegen, Geräte prüfen)', 'route' => ['GET', 'settings/ad-standorte']],

            // ─── Übungsfirmen ────────────────────────────────────────────
            ['area' => 'uebungsfirmen', 'label' => 'Übungsfirmen ansehen: Zugangsdaten je Standort (Windows & E-Mail, Lexware), kopieren', 'route' => ['GET', 'practice-companies']],
            ['area' => 'uebungsfirmen', 'label' => 'Übungsfirmen anlegen, ändern, löschen, Excel-Export', 'roles' => [], 'hint' => 'im Code nur Super_Admin (seit 2026-10-12)'],

            // ─── Administration ──────────────────────────────────────────
            ['area' => 'admin', 'label' => 'Einstellungen: Popup, Nachrichtenleiste, Standortbesuch, Adressbuch', 'route' => ['GET', 'settings/standortbesuch']],
            ['area' => 'admin', 'label' => 'Standard-Passwort für neue Mitarbeiter (Einstellungen › Neuer Mitarbeiter)', 'route' => ['GET', 'settings/neuer-mitarbeiter']],
            ['area' => 'admin', 'label' => 'Rollen, Berechtigungen, Benutzer', 'route' => ['GET', 'roles'], 'roles' => []],
            ['area' => 'admin', 'label' => 'Zuständigkeiten Handwerk und Korso (Rollen & Berechtigungen)', 'route' => ['GET', 'handwerk-zustaendigkeiten']],
            ['area' => 'admin', 'label' => 'Rollen-Übersicht (diese Seite)', 'route' => ['GET', 'rollen-uebersicht']],
            ['area' => 'admin', 'label' => 'Benutzer-Suche (nach Bundesland, Stadt, Adresse, Abteilung, Position, Rolle)', 'route' => ['GET', 'benutzer-suche']],
            ['area' => 'admin', 'label' => 'Erinnerungen verwalten', 'route' => ['GET', 'reminders']],

            // ─── Benachrichtigungen & Zuweisen (strict) ──────────────────
            ['area' => 'mail', 'label' => 'Zugewiesen, Erledigt, Kommentar oder Antwort auf eigene Tickets', 'open' => true, 'others' => 'Ersteller / Bearbeiter des Tickets'],
            ['area' => 'mail', 'label' => 'Neues IT-Ticket, Neuer Teilnehmer, IT-Ticket wiederhergestellt', 'roles' => ['Super_Admin'], 'strict' => true],
            ['area' => 'mail', 'label' => 'Kommentar auf einem nicht zugewiesenen IT-Ticket', 'roles' => ['Super_Admin'], 'strict' => true],
            ['area' => 'mail', 'label' => 'In „Zuweisen“ der IT-Tickets', 'roles' => ['Super_Admin'], 'strict' => true, 'hint' => 'admin steht nicht drin'],
            ['area' => 'mail', 'label' => 'Erinnerung: Standard-Passwort für neue Mitarbeiter ändern', 'roles' => ['Super_Admin'], 'strict' => true, 'hint' => '1.–7. Januar per Mail, bis zum Ändern auch im Dashboard'],
            ['area' => 'mail', 'label' => 'Neues oder wiederhergestelltes Handwerk-Ticket', 'roles' => ['Handwerk_verwaltung'], 'strict' => true],
            ['area' => 'mail', 'label' => 'Kommentar auf einem nicht zugewiesenen Handwerk-Ticket', 'roles' => ['Handwerk_verwaltung'], 'strict' => true],
            ['area' => 'mail', 'label' => 'Handwerk-Ticket von Sekretariat / Handwerker erledigt', 'roles' => ['Handwerk_verwaltung'], 'strict' => true],
            ['area' => 'mail', 'label' => 'In „Zuweisen“ der Handwerk-Tickets', 'roles' => ['handwerk_admin'], 'strict' => true, 'others' => '„Zuweisbar für Standorte“ (Rollen & Berechtigungen › Handwerk)'],
            ['area' => 'mail', 'label' => 'Neues Korso-Ticket mit Priorität Hoch', 'roles' => ['Korso_ma'], 'strict' => true, 'hint' => 'normal / niedrig: niemand (so gewollt)'],
            ['area' => 'mail', 'label' => 'Kommentar auf einem nicht zugewiesenen Korso-Ticket', 'roles' => ['Korso_ma'], 'strict' => true],
            ['area' => 'mail', 'label' => 'Neues Onlinemarketing-Ticket (jede Priorität)', 'roles' => ['Onlinemarketing'], 'strict' => true],
            ['area' => 'mail', 'label' => 'Nicht zugewiesenes Korso-Ticket wiederhergestellt', 'roles' => ['Korso_verwaltung'], 'strict' => true],
            ['area' => 'mail', 'label' => 'In „Zuweisen“ der Korso-Tickets', 'roles' => ['Korso_ma'], 'strict' => true, 'hint' => 'Korso_Admin nur mit zusätzlich Korso_ma'],
        ];
    }

    public static function build(): array
    {
        $routes = self::routeIndex();
        $capabilities = [];
        foreach (self::catalog() as $i => $c) {
            $capabilities[] = self::resolve($i + 1, $c, $routes);
        }

        $members = self::members();
        $dbRoles = DB::table('roles')->where('guard_name', 'web')->pluck('name')->all();

        $roles = [];
        foreach (self::ROLES as $key => $r) {
            $list = $members[$key] ?? [];
            $roles[] = [
                'key' => $key,
                'group' => $r['group'],
                'kind' => $r['kind'],
                'summary' => $r['summary'],
                'notes' => $r['notes'],
                'assign' => $r['assign'],
                'menu' => $r['menu'],
                'exists' => in_array($key, $dbRoles, true),
                'queried' => $r['queried'] ?? null,
                'count' => count($list),
                // Verwaltung = practically everyone: no name list
                'members' => $key === 'Verwaltung' ? [] : $list,
            ];
        }

        $known = array_keys(self::ROLES);
        $unknown = [];
        foreach ($dbRoles as $name) {
            if (! in_array($name, $known, true)) {
                $unknown[] = ['key' => $name, 'count' => count($members[$name] ?? []), 'members' => $members[$name] ?? []];
            }
        }

        // Active users without Verwaltung: they cannot create tickets.
        $withVerwaltung = array_column($members['Verwaltung'] ?? [], 'id');
        $without = User::whereNotIn('id', $withVerwaltung ?: [0]);

        return [
            'groups' => self::GROUPS,
            'areas' => self::AREAS,
            'roles' => $roles,
            'capabilities' => $capabilities,
            'unknownRoles' => $unknown,
            'activeUsers' => User::count(),
            'withoutVerwaltungCount' => (clone $without)->count(),
            'withoutVerwaltung' => $without->orderBy('name')->orderBy('vorname')->limit(200)
                ->get(['id', 'vorname', 'name', 'username', 'ort'])
                ->map(fn ($u) => self::person($u))->values()->all(),
        ];
    }

    private static function resolve(int $id, array $c, array $routes): array
    {
        $strict = (bool) ($c['strict'] ?? false);
        $scope = $c['scope'] ?? [];
        $roles = $c['roles'] ?? [];
        $source = (isset($c['roles']) || isset($c['only'])) ? 'Code' : null;
        $missingRoute = false;

        if (isset($c['route'])) {
            [$method, $uri] = $c['route'];
            $missingRoute = ! isset($routes[strtoupper($method).' '.$uri]);
            $fromTable = EnforceRouteAccess::rolesFor($method, $uri);
            if ($fromTable !== null) {
                $roles = array_merge(explode('|', $fromTable), $roles);
                $source = 'route_access'.($source ? ' + Code' : '');
            }
            $source = trim(($source ?? '').' · '.strtoupper($method).' /'.ltrim($uri, '/'), ' ·');
        }
        if (isset($c['only'])) {
            $roles = $c['only'];
        }

        // open = no role rule anywhere (route_access may add one later)
        $open = ($c['open'] ?? false) && $roles === [];

        $cells = [];
        foreach (array_unique($roles) as $role) {
            $cells[$role] = $scope[$role] ?? null;
        }
        if (! $strict && ! $open) {
            $cells['Super_Admin'] = $scope['Super_Admin'] ?? null;
        }

        return [
            'id' => $id,
            'area' => $c['area'],
            'label' => $c['label'],
            'hint' => $c['hint'] ?? null,
            'others' => $c['others'] ?? null,
            'open' => $open,
            'strict' => $strict,
            'cells' => (object) $cells,
            'source' => $source,
            'missingRoute' => $missingRoute,
        ];
    }

    /** "METHOD uri" => true for every registered route. */
    private static function routeIndex(): array
    {
        $index = [];
        foreach (Route::getRoutes() as $route) {
            foreach ($route->methods() as $m) {
                $index[$m.' '.$route->uri()] = true;
            }
        }

        return $index;
    }

    /** role name => members (users not soft-deleted), sorted by name. */
    private static function members(): array
    {
        $rows = DB::table('model_has_roles as m')
            ->join('roles as r', 'r.id', '=', 'm.role_id')
            ->join('users as u', 'u.id', '=', 'm.model_id')
            ->where('m.model_type', (new User)->getMorphClass())
            ->whereNull('u.deleted_at')
            ->orderBy('u.name')->orderBy('u.vorname')
            ->get(['r.name as role', 'u.id', 'u.vorname', 'u.name', 'u.username', 'u.ort']);

        $out = [];
        foreach ($rows as $row) {
            $out[$row->role][] = self::person($row);
        }

        return $out;
    }

    private static function person($u): array
    {
        return [
            'id' => (int) $u->id,
            'name' => trim(($u->vorname ?? '').' '.($u->name ?? '')) ?: (string) $u->username,
            'username' => (string) $u->username,
            'ort' => $u->ort,
        ];
    }
}
