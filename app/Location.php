<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    public function place()
    {
        return $this->belongsTo(Place::class,'place_id','id');
    }

    public function invrooms()
    {
        return $this->hasMany(InvRoom::class);
    }


}
