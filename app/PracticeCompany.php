<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PracticeCompany extends Model
{
        protected $fillable = [
        'Windows_Username', 
        'Windows_Password', 
        'Lexware_Username', 
        'Lexware_Password', 
        'Email_Username', 
        'Email_Password',
        'place_id', 
    ];

    public function place()
    {
      return $this->belongsTo(Place::class, 'place_id');
    }
}
