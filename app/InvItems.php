<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InvItems extends Model
{
    protected $guarded = [];

    public function invroom()
    {
        return $this->belongsTo(InvRoom::class,'room_id','id');
    }
    public function garts()
    {
        return $this->belongsTo(Gart::class,'gart_id','id');
    }
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function amgs()
    {
        return $this->belongsTo(Amg::class,'amg_id','id');
    }
    public function telUsers()
    {
        return $this->belongsToMany('App\User', 'inv_item_user', 'inv_item_id','user_id');
    }

    /**
     * Ported from the now-merged App\InvAbItem model: the "Anschaffungsdatum"
     * (purchase date) is displayed/consumed as d-m-Y wherever it's read
     * (e.g. the Ausmustern/Edit/Rename search AJAX responses). Guarded
     * against null since andat is no longer only ever read on a single
     * fetched record - InvItems collections (machinelist(), room listings,
     * etc.) can include rows that never had a purchase date recorded.
     */
    public function getAndatAttribute()
    {
        return isset($this->attributes['andat']) && $this->attributes['andat']
            ? Carbon::createFromFormat('Y-m-d', $this->attributes['andat'])->format('d-m-Y')
            : null;
    }
}
