<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
  public $fillable = ['pnname'];

  public function locations()
  {
    return $this->hasMany(Location::class);
  }
  public function notes()
  {
    return $this->hasMany(Note::class);
  }
      public function practiceCompanies()
    {
        return $this->hasMany(PracticeCompany::class, 'place_id');
    }
}
