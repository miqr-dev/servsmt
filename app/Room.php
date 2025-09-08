<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use LdapRecord\Laravel\LdapImportable;
use LdapRecord\Laravel\ImportableFromLdap;

class Room extends Model implements LdapImportable

{
use ImportableFromLdap;
    //
}
