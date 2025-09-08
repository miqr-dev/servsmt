<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payer extends Model
{
    protected $table = 'payers';
    protected $fillable = ['name'];

    public function kcourses()
    {
        return $this->hasMany(Kcourse::class);
    }
}
