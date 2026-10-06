<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** AD organizational unit (= room / location level), imported by ad:import-inventory. */
class AdOu extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['synced_at' => 'datetime'];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function computers()
    {
        return $this->hasMany(AdComputer::class, 'ad_ou_id');
    }
}
