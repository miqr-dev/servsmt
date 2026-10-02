<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default LDAP Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the LDAP connections below you wish
    | to use as your default connection for all LDAP operations. Of
    | course you may add as many connections you'd like below.
    |
    */

    'default' => env('LDAP_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | LDAP Connections
    |--------------------------------------------------------------------------
    |
    | Below you may configure each LDAP connection your application requires
    | access to. Be sure to include a valid base DN - otherwise you may
    | not receive any results when performing LDAP search operations.
    |
    */

    'connections' => [

        'default' => [
            'hosts' => [env('LDAP_HOST', '127.0.0.1')],
            'username' => env('LDAP_USERNAME', 'cn=user,dc=local,dc=com'),
            'password' => env('LDAP_PASSWORD', 'secret'),
            'port' => env('LDAP_PORT', 389),
            'base_dn' => env('LDAP_BASE_DN', 'dc=local,dc=com'),
            'timeout' => env('LDAP_TIMEOUT', 5),
            'use_tls' => env('LDAP_TLS', false),
            'use_starttls' => env('LDAP_STARTTLS', false),
        ],

        // Profile -> AD write-back (config/ad_writeback.php). Separate service
        // account that may write ONLY the profile attributes (delegated in AD);
        // the import account above stays read-only. Same server, LDAPS.
        'writer' => [
            'hosts' => [trim((string) env('LDAP_WRITE_HOST', env('LDAP_HOST', '127.0.0.1')))],
            'username' => env('LDAP_WRITE_USERNAME', ''),
            'password' => env('LDAP_WRITE_PASSWORD', ''),
            'port' => env('LDAP_WRITE_PORT', 636),
            'base_dn' => env('LDAP_WRITE_BASE_DN', env('LDAP_BASE_DN', 'dc=local,dc=com')),
            'timeout' => env('LDAP_TIMEOUT', 5),
            // ldaps:// (LdapRecord 4: use_tls = LDAPS on port 636)
            'use_tls' => env('LDAP_WRITE_TLS', true),
            'use_starttls' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP Logging
    |--------------------------------------------------------------------------
    |
    | When LDAP logging is enabled, all LDAP search and authentication
    | operations are logged using the default application logging
    | driver. This can assist in debugging issues and more.
    |
    */

    'logging' => env('LDAP_LOGGING', true),

    /*
    |--------------------------------------------------------------------------
    | LDAP Cache
    |--------------------------------------------------------------------------
    |
    | LDAP caching enables the ability of caching search results using the
    | query builder. This is great for running expensive operations that
    | may take many seconds to complete, such as a pagination request.
    |
    */

    'cache' => [
        'enabled' => env('LDAP_CACHE', false),
        'driver' => env('CACHE_DRIVER', 'file'),
    ],

];
