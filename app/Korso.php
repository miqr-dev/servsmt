<?php

namespace App;

use App\KorsoItem;
use App\KorsoAttachment;
use Laravelista\Comments\Comment;
use Laravelista\Comments\Commentable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Korso extends Model
{
  use SoftDeletes, Commentable;

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
  public function comments()
  {
    return $this->morphMany(Comment::class, 'commentable')->withTrashed();
  }
  public function sekGroup()
  {
    return $this->belongsTo(SekGroup::class);
  }
}
