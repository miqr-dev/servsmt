<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
  public $fillable = ['content','place_id'];

  public function place()
{
    return $this->belongsTo(Place::class);
}

}
