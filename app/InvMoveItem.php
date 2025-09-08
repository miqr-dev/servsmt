<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvMoveItem extends Model
{
  public function room()
  {
    return $this->belongsTo('App\InvRoom','room_id','id');
  }

   public function room_old()
  {
    return $this->belongsTo('App\InvRoom','room_id_old','id');
  }
}
