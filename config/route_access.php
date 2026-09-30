<?php

/*
|--------------------------------------------------------------------------
| Route access map (2026-09-28)
|--------------------------------------------------------------------------
|
| Which roles may use which URL. Enforced for every web request by
| App\Http\Middleware\EnforceRouteAccess. Before this, most pages were only
| HIDDEN (menu links), not protected - anyone logged in via Windows SSO could
| open e.g. the IT admin ticket lists by typing the URL.
|
| Keys: the route's URI as Laravel defines it (no leading slash, with its
| {parameters}), optionally prefixed by an HTTP method ("DELETE korso/{korso}").
| `*` is a wildcard ("settings/*"). The FIRST matching key wins, so specific
| entries must come before broader wildcard ones.
|
| Values: roles separated by "|"; the user needs any one of them.
| Super_Admin always passes (it counts as every role - App\User::hasRole).
|
| Routes NOT listed are open to every logged-in user (e.g. Meine Tickets,
| profile, comments, notifications, news checks). Pages that need an
| "owner OR staff" rule (ticket / Korso / Handwerk detail + restore) check
| that in their controller instead, because a role alone can't express it.
|
| Controllers that already had their own checks keep them (roles/users/
| permissions, settings index, contacts index, matrix, inventory index,
| ticket picker, Teilnehmer Liste, Evaluationen, Printmarketing Verwaltung,
| special tickets).
*/

$IT_ADMIN = 'Super_Admin|admin';
$VERWALTUNG = 'Verwaltung';
$KORSO_STAFF = 'Korso_ma|Korso_Admin';
$KORSO_ADMIN = 'Korso_Admin';
$HW_STAFF = 'handwerk_admin|handwerk';
$HW_ADMIN = 'handwerk_admin';
$INV = 'INV';
$HR = 'HR';
$SUPER = 'Super_Admin';

return [

    // ─── IT tickets: admin side ─────────────────────────────────────────
    'opentickets' => $IT_ADMIN,
    'unassignedtickets' => $IT_ADMIN,
    'tickethistory' => $IT_ADMIN,
    'tickets/city/{cityName}' => $IT_ADMIN,
    'tickets/{userId?}' => $IT_ADMIN,
    'city/{city}/tickets/pdf' => $IT_ADMIN,
    'getCityTicketsDetails/{cityName}' => $IT_ADMIN,
    'ticket.delete/{myTicket}' => $IT_ADMIN,      // Erledigt
    'ticket.delete2/{myTicket}' => $IT_ADMIN,     // Erledigt 2 (Neuer Mitarbeiter)
    'ticket.force_delete/{id}' => $IT_ADMIN,
    'ticket/assignTo' => $IT_ADMIN,
    'ticket/priority' => $IT_ADMIN,
    'ticket/status' => $IT_ADMIN,
    'ticket/{ticketId}/forwarding-removed' => $IT_ADMIN,
    'ticket/admin_notes' => $IT_ADMIN,
    'ticket/employee_*' => $IT_ADMIN,
    'ticket/update-remark/{city}' => $IT_ADMIN,
    'ticket/on_location*' => $IT_ADMIN,
    'ticket/set-reminder' => $IT_ADMIN,
    'participant.username' => $IT_ADMIN,
    'notes' => $IT_ADMIN,
    'notes/{note}' => $IT_ADMIN,
    'participants/create' => $IT_ADMIN,
    'POST participants' => $IT_ADMIN,              // store stub (unused)
    'participants/{participant}' => $IT_ADMIN,     // incl. DELETE from the ticket page
    'participants/{participant}/edit' => $IT_ADMIN,
    // old per-person / per-city debug views
    'individual/*' => $SUPER,
    'mammach' => $SUPER,
    'basti' => $SUPER,
    'ara' => $SUPER,
    'rolf' => $SUPER,
    'berlin' => $SUPER,
    'chemnitz' => $SUPER,
    'dresden' => $SUPER,
    'leipzig' => $SUPER,
    'suhl' => $SUPER,
    'döbeln' => $SUPER,

    // ─── IT tickets: creating (Verwaltung = every employee) ──────────────
    'ticket.restore/{myTicket}' => null,          // owner or IT admin - checked in controller
    'ticket.*' => $VERWALTUNG,                    // all creation forms + their AJAX lookups
    'form_store' => $VERWALTUNG,
    'problem_type' => $VERWALTUNG,
    'dependant_forms' => $VERWALTUNG,
    'problem_type_machine' => $VERWALTUNG,
    'donwload_muster' => $VERWALTUNG,
    'ticket/address' => $VERWALTUNG,

    // ─── Korso ──────────────────────────────────────────────────────────
    'korso-dashboard' => $KORSO_STAFF,
    'dashboard/filter-tickets' => $KORSO_STAFF,
    'dashboard/mitarbeiter-suche' => $VERWALTUNG,   // Mitarbeiter Info (Dashboard)
    'korso/{id}/details' => $KORSO_STAFF,
    'korso/assign' => $KORSO_STAFF,
    'korso/update-status/{id}' => $KORSO_STAFF,
    'korso/{korso}/done' => $KORSO_STAFF,
    'DELETE korso/{korso}' => $KORSO_STAFF,
    'korso/{id}/upload-attachment' => $KORSO_STAFF,
    'korso/{korso}/attachment/{attachment}' => $KORSO_STAFF,
    'korso/{id}/download-pdf' => $KORSO_STAFF,
    'korso/comments/*' => $KORSO_STAFF,           // internal notes
    'korso/force_delete/{id}' => $KORSO_ADMIN,
    'user-management' => $KORSO_ADMIN,
    'assign-role' => $KORSO_ADMIN,
    'remove-role' => $KORSO_ADMIN,
    'onlinemarketing_items*' => $KORSO_ADMIN,
    'zertifizierung_items*' => $KORSO_ADMIN,
    'sek-groups*' => $KORSO_ADMIN,
    'korso/{korso}/restore' => null,              // owner or Korso staff - controller
    'korso' => $VERWALTUNG,
    'printmarketing' => $VERWALTUNG,
    'onlinemarketing' => $VERWALTUNG,
    'zertifizierung' => $VERWALTUNG,
    'form_store_korso' => $VERWALTUNG,

    // ─── Handwerk ───────────────────────────────────────────────────────
    'handwerker/*' => $HW_STAFF,                  // per-city lists + ToDos
    'handwerk.delete/{myhandwerk}' => $HW_STAFF.'|Sekretariat',  // Erledigt - Sekretariat could in the old app too
    'handwerk/ajax-destroy/{id}' => $HW_STAFF,
    'handwerk/assignTo' => $HW_ADMIN,
    'handwerk/admin_notes' => $HW_ADMIN,
    'handwerk/{city}/open-tickets-pdf' => 'handwerk_admin|Sekretariat',
    'handwerk.restore/{myhandwerk}' => null,      // owner or Handwerk staff - controller
    'handwerk' => $VERWALTUNG,
    'einrichtungsgegenstände' => $VERWALTUNG,
    'elektro' => $VERWALTUNG,
    'neustandort' => $VERWALTUNG,
    'reparatur_elektro' => $VERWALTUNG,
    'reparatur_mobiliar' => $VERWALTUNG,
    'modifikation' => $VERWALTUNG,
    'form_store_handwerk' => $VERWALTUNG,
    'my-handwerks-tickets' => $VERWALTUNG,
    'usertHandwerkicketshistory' => $VERWALTUNG,
    'room/list/{city?}' => $VERWALTUNG,
    'room/lists' => $VERWALTUNG,

    // ─── Inventory (item/listen stays open: the ticket forms use it) ────
    'sync' => $INV,
    'auto' => $INV,
    'search_missing_label' => $INV,
    'machinelist*' => $INV,
    'item/listen' => null,
    'item' => $INV,
    'item/*' => $INV,
    'item_man' => $INV,
    'item_move_*' => $INV,
    'dropzone/*' => $INV,
    'search' => $INV,
    'search_*' => $INV,                           // search_edit, search_check_*, search_move, … (NOT searchbyname)
    'isClassRoom' => $INV,
    'moveToTeil' => $INV,
    'invalid' => $INV,
    'print/*' => $INV,
    'printmissing/*' => $INV,
    'room_listen' => $INV,
    'room_inventur' => $INV,
    'room/inventur/*' => $INV,
    'inventur_store_final' => $INV,
    'auto_change_location' => $INV,
    'send_unordered_computers' => $INV,
    'print_inventur' => $INV,
    'inventoryblade' => $INV,
    'devices*' => $SUPER,
    'device-statuses*' => $SUPER,

    // ─── Settings & admin tools (Super_Admin) ───────────────────────────
    'settings/*' => $SUPER,
    'create_city' => $SUPER,
    'create_address' => $SUPER,
    'create_room' => $SUPER,
    'popup_update/1' => $SUPER,
    'newsbar_update/1' => $SUPER,
    'contacts/*' => 'Super_Admin|admin',
    'address' => 'Super_Admin|admin',
    'rooms' => 'Super_Admin|admin',
    'searchbyname' => 'Super_Admin|admin',
    'searchbyusername' => 'Super_Admin|admin',
    'tasks' => $SUPER,
    'projects*' => $SUPER,
    'reminders*' => $SUPER,
    'umcategories*' => $SUPER,
    'documents*' => $SUPER,
    'practice-companies*' => 'Terminal',

    // ─── HR ─────────────────────────────────────────────────────────────
    'licenses*' => 'Super_Admin',                 // Lizenzen: Super_Admin only (2026-09-29)
    'terminations*' => $HR,
    'terminations.delete/{id}' => $HR,
    'employees*' => $HR,
];
