<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
  protected $fillable = ['name'];

  public function statuses()
  {
    return $this->hasMany(DeviceStatus::class);
  }
}
