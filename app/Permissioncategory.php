<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permissioncategory extends Model
{
  public function permissions()
  {
      return $this->hasMany('Spatie\Permission\Models\Permission');
  }
}
