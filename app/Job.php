<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
  protected $dates = [
    'created_at',
    'updated_at'
  ];
  
  public function workerId()
  {
    return $this->belongsTo('App\User','worker_id','id');
  }

  function getFormattedCreatedAtAttribute()
  {
  // return $this->created_at->format('j F, Y');
  return $this->created_at->format('d-m-Y');
  }
}
