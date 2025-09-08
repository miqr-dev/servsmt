<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SekGroup extends Model
{
  public $fillable  = ['name', 'email'];
  
  public function users()

  {
    return $this->belongsToMany(User::class, 'sek_group_user');
  }
}
