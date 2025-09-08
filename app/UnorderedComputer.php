<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnorderedComputer extends Model
{
  public function room()
  {
    return $this->belongsTo('App\InvRoom','room_id','id');
  }
}
