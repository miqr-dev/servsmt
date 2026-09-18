<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Concerns\Commentable;

class Ticket extends Model
{
  use SoftDeletes;
  use Commentable;

  // Eloquent snake_cases relation keys by default when a model is
  // serialized to array/JSON (Model::$snakeAttributes, inherited as true) -
  // e.g. the subUser() relation below would come back as "sub_user" in the
  // Inertia props, not "subUser". Every Vue page for this module (Tickets/
  // AdminList.vue, Tickets/UserTickets.vue, Tickets/UserTicketsHistory.vue,
  // Dashboard.vue's forwarding tables) was written expecting the relation
  // methods' own camelCase names (subUser, forwardOnUser, forwardFromUser,
  // forwardRemovedByUser, specialComments), so without this override those
  // props were silently always undefined - same bug, same fix as
  // App\Korso's $snakeAttributes override (see that model's comment).
  public static $snakeAttributes = false;

  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'participant_required_at' => 'datetime',
    'forward_required_at' => 'datetime',
    'forward_to_at' => 'datetime',
    'forward_removed_at' => 'datetime',
    'employee_required_at' => 'datetime',
    'employee_finish_at' => 'datetime',
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

  public function pcs()
  {
    return $this->belongsToMany('App\InvItems', 'ticket_pcs', 'ticket_id', 'inv_item_id')->withTimestamps();
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

  public function forwardOnUser()
  {
    return $this->belongsTo('App\User', 'forward_on', 'id')->withTrashed();
  }

  public function forwardFromUser()
  {
    return $this->belongsTo('App\User', 'forward_from', 'id')->withTrashed();
  }

  public function forwardRemovedByUser()
  {
    return $this->belongsTo('App\User', 'forward_removed_by', 'id')->withTrashed();
  }

  public function specialComments()
  {
    return $this->hasMany(TicketSpecialComment::class)->latest('created_at');
  }
}
