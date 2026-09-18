<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Standortbesuch extends Model
{
  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'berlin' => 'datetime',
    'berlinii' => 'datetime',
    'chemnitz' => 'datetime',
    'dresden' => 'datetime',
    'leipzig' => 'datetime',
    'suhl' => 'datetime',
  ];


   function getFormattedBerlinAttribute()
  {
    return $this->berlin->format('d-m-Y');
  }
  function getFormattedBerliniiAttribute()
  {
    return $this->berlinii->format('d-m-Y');
  }
  function getFormattedChemnitzAttribute()
  {
    return $this->chemnitz->format('d-m-Y');
  }
  function getFormattedDresdenAttribute()
  {
    return $this->dresden->format('d-m-Y');
  }
   function getFormattedLeipzigAttribute()
  {
    return $this->leipzig->format('d-m-Y');
  }
   function getFormattedSuhlAttribute()
  {
    return $this->suhl->format('d-m-Y');
  }
}
