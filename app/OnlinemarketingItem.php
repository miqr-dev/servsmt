<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnlinemarketingItem extends Model
{
  use SoftDeletes;
  
  protected $fillable = ['name'];

  public function korsos()
  {
    return $this->hasMany(Korso::class);
  }
}
