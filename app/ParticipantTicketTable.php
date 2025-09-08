<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ParticipantTicketTable extends Model
{
  use SoftDeletes;

  public function ticket()
  {
    return $this->belongsTo('App\Ticket','ticket_id', 'id')->withTrashed();
  }

  protected $dates = [
    'created_at',
    'updated_at',
  ];

  protected $guarded = [];


  function getFormattedCreatedAtAttribute()
  {
    // return $this->created_at->format('j F, Y');
    return $this->created_at->format('d-m-Y');
  }
}
