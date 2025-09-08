<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kcourse extends Model
{
    protected $table = 'kcourses';
    protected $fillable = ['payer_id', 'name'];

    public function payer()
    {
        return $this->belongsTo(Payer::class);
    }
        public function korsos()
    {
        return $this->belongsToMany(Korso::class, 'kcourse_korso')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}
