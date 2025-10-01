<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Termination extends Model
{
  use SoftDeletes;
  protected $dates = ['created_at', 'updated_at', 'exit'];
  protected $guarded = [];
  protected $casts = [
    'is_active' => 'boolean',
  ];
}
