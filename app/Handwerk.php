<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


class Handwerk extends Model
{
  use SoftDeletes;

  protected $dates = [
    'created_at',
    'updated_at',
    'deleted_at',
    'submit_date',
  ];
  protected $guarded = [];

  public function subUser()
  {
    return $this->belongsTo('App\User', 'submitter', 'id')->withTrashed();
  }
  public function location()
  {
    return $this->belongsTo('App\Location', 'location_id', 'id');
  }
  public function room()
  {
    return $this->belongsTo('App\InvRoom', 'room_id', 'id');
  }
  public function todos()
  {
    return $this->hasMany(HandwerkTodo::class, 'ticket_id');
  }
}
