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
    'valid' => 'datetime',
  ];

}
