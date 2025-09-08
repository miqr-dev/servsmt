<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Umfrage extends Model
{
  protected $fillable = ['place_id', 'title', 'url','umcategory_id'];

  public function place()
  {
    return $this->belongsTo(Place::class);
  }
  public function umcategory()
{
    return $this->belongsTo(Umcategory::class);
}
}
