<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * DEPRECATED as of the inv_items/inv_ab_items merge (see App\InvItems).
 * inv_ab_items is no longer written to by the app - every column here
 * now also lives on inv_items, which is the model actually used going
 * forward. Left in place, unused, until inv_ab_items itself is dropped
 * in a later, separate step once the merge has been verified.
 */
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
