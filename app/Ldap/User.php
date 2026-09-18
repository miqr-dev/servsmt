<?php

namespace App\Ldap;

use App\Ldap\Scopes\ImportFilter;
use LdapRecord\Models\ActiveDirectory\User as BaseModel;

class User extends BaseModel
{
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new ImportFilter);
    }
}
