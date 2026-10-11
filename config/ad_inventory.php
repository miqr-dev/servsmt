<?php

/*
| Import of rooms (AD OUs) and computers from Active Directory
| (php artisan ad:import-inventory -> ad_ous / ad_computers). Read-only on AD.
*/

return [
    // everything below this OU is imported
    'base' => env('AD_INVENTORY_BASE', 'OU=M-I-Q-R,DC=miqr,DC=local'),

    // LDAP connection from config/ldap.php (the read-only import account is enough)
    'connection' => env('AD_INVENTORY_CONNECTION', 'default'),

    // Bundesland of each AD Standort (by its label), groups the Standort select
    // of the ticket forms. Changed / added on Einstellungen > Standorte &
    // Adressen (stored in ad_ous.state, which wins over this list).
    'states' => [
        'Berlin' => ['Berlin'],
        'Sachsen' => ['Chemnitz', 'Döbeln', 'Dresden', 'Leipzig', 'Riesa'],
        'Thüringen' => ['Erfurt', 'Suhl'],
    ],
];
