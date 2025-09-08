<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZertifizierungItem extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'location_needed', 'massnahme_needed'];
}
