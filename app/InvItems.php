<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
    public function invabitem()
    {
        return $this->belongsTo(InvAbItem::class,'id','id');
    }
    public function telUsers()
    {
        return $this->belongsToMany('App\User', 'inv_item_user', 'inv_item_id','user_id');
    }
}
