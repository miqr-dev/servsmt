<?php

namespace App;

use Laravelista\Comments\Commentable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
  use SoftDeletes, Commentable;

  protected $dates = [
    'created_at',
    'updated_at',
    'start_date',
    'end_date',
  ];

  protected $fillable = [
    'who_created', 'assignedTo', 'ticket_id', 'location_id', 'name',
    'description', 'start_date', 'end_date', 'status'
  ];

  public function createdby()
  {
    return $this->belongsTo('App\User', 'who_created', 'id');
  }
  public function assignedTo()
  {
    return $this->belongsTo('App\User', 'assignedTo', 'id');
  }

  public function tickets()
  {
    return $this->belongsToMany(Ticket::class, 'project_ticket'); // Assume 'project_ticket' is your pivot table
  }


  public function location()
  {
    return $this->belongsTo(Location::class);
  }
  public function devices()
  {
    return $this->belongsToMany(Device::class, 'project_device')
      ->withPivot('quantity', 'status', 'note');
  }
  public function jobs()
  {
    return $this->hasMany(Job::class);
  }

  function getFormattedStartDateAttribute()
  {
    return $this->start_date->format('d-m-Y');
  }

  function getFormattedEndDateAttribute()
  {
    return $this->end_date->format('d-m-Y');
  }
  function getFormattedCreatedAtAttribute()
  {
    // return $this->created_at->format('j F, Y');
    return $this->created_at->format('d-m-Y');
  }
}
