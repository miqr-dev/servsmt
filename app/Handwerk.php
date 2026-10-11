<?php

namespace App;

use Carbon\Carbon;
use App\Concerns\Commentable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


class Handwerk extends Model
{
  use SoftDeletes;
  use Commentable;

  // Same fix as App\Korso and App\Ticket - Eloquent snake_cases relation
  // keys by default when a model is serialized to array/JSON
  // (Model::$snakeAttributes, inherited as true), so the subUser() relation
  // below would come back as "sub_user" in the Inertia props, not
  // "subUser" (Handwerk/Show.vue reads it as subUser).
  public static $snakeAttributes = false;

  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'submit_date' => 'datetime',
  ];
  protected $guarded = [];

  // AD room for display (AD switch step 3, 2026-10-09): pages show it and
  // fall back to the old room when it is empty. See App\Support\AdLocation.
  protected $appends = ['ad_room'];

  public function getAdRoomAttribute()
  {
    return \App\Support\AdLocation::room($this->attributes['ad_room_id'] ?? null);
  }

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
  // Assigned Handwerker/admin (column assignedTo). withTrashed so people who
  // left AD still show by name on old tickets.
  public function assignedUser()
  {
    return $this->belongsTo('App\User', 'assignedTo', 'id')->withTrashed();
  }
  public function todos()
  {
    return $this->hasMany(HandwerkTodo::class, 'ticket_id');
  }

  // AD room (2026-10-05, step 2). withTrashed: deleted rooms still show on old tickets.
  // (named adRoomOu, not adRoom: $handwerk->ad_room is the display array, see getAdRoomAttribute)
  public function adRoomOu()
  {
    return $this->belongsTo(AdOu::class, 'ad_room_id')->withTrashed();
  }
}
