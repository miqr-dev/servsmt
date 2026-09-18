<?php

namespace App;

use App\KorsoItem;
use App\KorsoAttachment;
use App\Concerns\Commentable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Korso extends Model
{
  use SoftDeletes;
  use Commentable;

  // Eloquent snake_cases relation keys by default when a model is
  // serialized to array/JSON (Model::$snakeAttributes, inherited as true) -
  // e.g. the subUser() relation below would come back as "sub_user" in the
  // Inertia props, not "subUser". Every Vue page for this module was
  // written expecting the relation methods' own camelCase names (subUser,
  // korsoItems, korsoAttachments, internalComments, onlinemarketingItem,
  // zertifizierungItem, assignedUser, doneByUser, sekGroup), so without
  // this override those props were silently always undefined - masked
  // elsewhere by fallbacks (submitter_name) or defensive "?? []" guards
  // rather than actually fixed. This only affects relation *keys*; plain
  // column attributes below are already snake_case in the DB either way.
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
  public function doneByUser()
  {
    return $this->belongsTo(User::class, 'done_by', 'id')->withTrashed();
  }
  public function location()
  {
    return $this->belongsTo('App\Location', 'location_id', 'id');
  }
  public function room()
  {
    return $this->belongsTo('App\InvRoom', 'room_id', 'id');
  }
  public function kcourses()
  {
    // The second argument is the pivot table name (if you want to be explicit).
    // Because of alphabetical naming, you can omit it if your table is exactly "kcourse_korso".
    return $this->belongsToMany(Kcourse::class, 'kcourse_korso')
      ->withPivot('quantity')
      ->withTimestamps();
  }

  public function ticket_status()
  {
    return $this->belongsTo(TicketStatus::class);
  }

  public function assignedUser()
  {
    return $this->belongsTo(User::class, 'assignedTo');
  }
  public function internalComments()
  {
    return $this->hasMany(KorsoInternalComment::class, 'korso_id')->orderBy('created_at', 'desc');
  }
  public function korsoItems()
  {
    return $this->hasMany(KorsoItem::class, 'korso_id');
  }
  public function korsoAttachments()
  {
    return $this->hasMany(KorsoAttachment::class, 'korso_id');
  }
  public function onlinemarketingItem()
  {
    return $this->belongsTo(OnlinemarketingItem::class, 'onlinemarketing_item');
  }
  public function zertifizierungItem()
  {
    return $this->belongsTo(ZertifizierungItem::class);
  }

  public function massnahme()
  {
    return $this->belongsTo(Massnahme::class);
  }
  public function sekGroup()
  {
    return $this->belongsTo(SekGroup::class);
  }
}
