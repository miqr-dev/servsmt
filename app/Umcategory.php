<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Umcategory extends Model
{
    protected $fillable = ['name'];

    public function umfrages()
{
    return $this->hasMany(Umfrage::class, 'umcategory_id');
}
}


