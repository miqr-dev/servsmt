<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvRoom extends Model
{
  public function location()
  {
      return $this->belongsTo(Location::class,'location_id','id');
  }
  public function invitems()
  {
      return $this->hasMany(InvItems::class,'room_id','id');
  }
  public function subRooms()
  {
      return $this->hasMany(InvSubRooms::class,'room_id','id');
  }
}
