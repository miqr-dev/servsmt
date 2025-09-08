<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KorsoAttachment extends Model
{
  protected $guarded = [];

  public function korso()
  {
    return $this->belongsTo(Korso::class, 'korso_id');
  }
}
