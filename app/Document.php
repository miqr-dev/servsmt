<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['name'];

    public function variables()
    {
        return $this->hasMany(DocumentVariable::class);
    }
}
