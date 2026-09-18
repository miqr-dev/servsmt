<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Termination extends Model
{
  use SoftDeletes;
  protected $guarded = [];
  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'exit' => 'datetime',
    'is_active' => 'boolean',
  ];
}
