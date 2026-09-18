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
