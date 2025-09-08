<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KorsoInternalComment extends Model
{
  protected $fillable = ['korso_id', 'user_id', 'comment', 'is_deleted'];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function korso()
  {
    return $this->belongsTo(Korso::class);
  }
}
