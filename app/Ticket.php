<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravelista\Comments\Commentable;

class Ticket extends Model
{
  use SoftDeletes, Commentable;

  protected $dates = [
    'created_at',
    'updated_at',
    'deleted_at',
    'participant_required_at',
    'forward_required_at',
    'employee_required_at',
    'employee_finish_at'
  ];

  public $fillable = ['name_participant', 'vorname_participant', 'course_participant', 'notes_participant'];

  public function user()
  {
    return $this->belongsTo('App\User', 'assignedTo', 'id');
  }
  public function subUser()
  {
    return $this->belongsTo('App\User', 'submitter', 'id')->withTrashed();
  }
  public function invitem()
  {
    return $this->belongsTo('App\InvItems', 'gname_id', 'id');
  }
  public function printer()
  {
    return $this->belongsTo('App\InvItems', 'printer_name', 'invnr');
  }
  public function ticket_status()
  {
    return $this->belongsTo('App\TicketStatus', 'ticket_status_id', 'id');
  }
  public function ticket_priority()
  {
    return $this->belongsTo('App\TicketPriority', 'priority_id', 'id');
  }
  public function gart()
  {
    return $this->belongsTo('App\Gart', 'gart_id', 'id');
  }
  public function location()
  {
    return $this->belongsTo('App\Location', 'location_id', 'id');
  }
  public function room()
  {
    return $this->belongsTo('App\InvRoom', 'room_id', 'id');
  }
  public function replication()
  {
    return $this->belongsTo('App\User', 'replication_id', 'id');
  }
  public function participanttickettables()
  {
    return $this->hasMany('App\ParticipantTicketTable', 'ticket_id');
  }

  function getFormattedParticipantRequiredAtAttribute()
  {
    return $this->participant_required_at->format('d-m-Y');
  }
  function getFormattedEmployeeRequiredAtAttribute()
  {
    return $this->participant_required_at->format('d-m-Y');
  }
  public function reminders()
  {
    return $this->hasMany(Reminder::class);
  }
}
