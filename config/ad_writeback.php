<?php

/*
|--------------------------------------------------------------------------
| Active Directory write-back (Profil -> AD)
|--------------------------------------------------------------------------
|
| Saving the profile (SettingController@firstupdate) writes the profile fields
| straight into the user's AD account via LdapRecord (job
| App\Jobs\SyncUserToActiveDirectory). This is the only AD sync - the old
| updateuser.csv -> Zabbix -> PowerShell chain was removed (2026-10-02).
|
| - enabled:    AD_WRITEBACK=true turns it on. Off = profile changes stay in
|               servsmt only (e.g. local dev without AD).
| - connection: LDAP connection with WRITE rights (config/ldap.php "writer",
|               separate service account, LDAPS). The import account stays
|               read-only.
| - attributes: servsmt users column => AD attribute. ONLY these are ever
|               written (same mapping as the ldap:import sync_attributes in
|               config/auth.php, so import and write-back agree). Name,
|               Vorname, samaccountname, mail, groups, passwords: never.
|
*/

return [
    'enabled' => (bool) env('AD_WRITEBACK', false),

    'connection' => env('AD_WRITEBACK_CONNECTION', 'writer'),

    'attributes' => [
        'position' => 'title',                          // Tätigkeit
        'abteilung' => 'department',
        'tel' => 'telephoneNumber',
        'fax' => 'facsimileTelephoneNumber',
        'ort' => 'l',
        'straße' => 'streetAddress',
        'plz' => 'postalCode',
        'title' => 'personalTitle',                     // Frau / Herr / Dr. ...
        'mobil' => 'mobile',
        'privat' => 'otherHomePhone',
        'email_privat' => 'url',
        'abschluss' => 'info',
        'office' => 'physicalDeliveryOfficeName',       // BusinessUnit
    ],

    // Failed syncs are retried by `ad:writeback --failed` (scheduled hourly)
    // for this many days, then given up (still visible in ad_sync_logs).
    'retry_days' => (int) env('AD_WRITEBACK_RETRY_DAYS', 3),
];
