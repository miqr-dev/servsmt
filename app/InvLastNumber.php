<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvLastNumber extends Model
{
    public function location()
    {
        return $this->belongsTo(Location::class,'location_id','id');
    }
    public function room()
    {
        return $this->hasMany(InvRoom::class,'location_id','location_id');
    }
}
