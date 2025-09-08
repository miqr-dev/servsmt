<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InvAbItem extends Model
{
    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function garts()
    {
        return $this->belongsTo(Gart::class,'gart_id','id');
    }
    public function amgs()
    {
        return $this->belongsTo(Amg::class,'amg_id','id');
    }

    public function getAndatAttribute()
    {
        return Carbon::createFromFormat('Y-m-d',$this->attributes['andat'])->format('d-m-Y');
    }




}
