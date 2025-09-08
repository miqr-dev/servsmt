<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeviceStatus extends Model
{
  protected $fillable = ['name', 'device_id'];

  public function device()
  {
    return $this->belongsTo(Device::class);
  }
}
