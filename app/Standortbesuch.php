<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Standortbesuch extends Model
{
  protected $dates = [
    'created_at',
    'updated_at',
    'berlin',
    'berlinii',
    'chemnitz',
    'dresden',
    'leipzig',
    'suhl',
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
