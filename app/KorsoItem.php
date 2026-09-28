<?php

namespace App;

use App\Korso;
use Illuminate\Database\Eloquent\Model;

class KorsoItem extends Model
{
    protected $guarded = [];

    // Always a real boolean in JSON (Inertia props), never "0"/"1" strings -
    // Korso/Show.vue strikes through ordered items based on it.
    protected $casts = ['ordered' => 'boolean'];

    public function korso()
    {
        return $this->belongsTo(Korso::class, 'korso_id');
    }
}
