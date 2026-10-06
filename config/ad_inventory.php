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
];
