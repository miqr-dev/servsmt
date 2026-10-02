<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class License extends Model
{
use SoftDeletes;


    protected $guarded = [];

    protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    // plain Y-m-d in JSON (a datetime cast shifts midnight Berlin to the previous day in UTC)
    'valid' => 'date:Y-m-d',
  ];

}
