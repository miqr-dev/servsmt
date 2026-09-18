<?php

namespace App\Ldap\Scopes;

use LdapRecord\Models\Model;
use LdapRecord\Models\Scope;
use LdapRecord\Query\Model\Builder;

class ImportFilter implements Scope
{
  public function apply(Builder $query, Model $model): void
  {
    $query->whereEquals(
      'memberof',
      'CN=Verwaltung,OU=Verwaltung,OU=Verteiler,OU=Standort Erfurt,OU=M-I-Q-R,DC=miqr,DC=local'
    );
  }
}
