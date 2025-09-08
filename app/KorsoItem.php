<?php

namespace App;

use App\Korso;
use Illuminate\Database\Eloquent\Model;

class KorsoItem extends Model
{
    protected $guarded = [];

    public function korso()
    {
        return $this->belongsTo(Korso::class, 'korso_id');
    }
}
