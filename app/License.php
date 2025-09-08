<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class License extends Model
{
use SoftDeletes;


    protected $guarded = [];

    protected $dates = [
    'created_at',
    'updated_at',
    'valid',
  ];

}
