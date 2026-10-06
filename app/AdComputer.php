<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** AD computer object, imported by ad:import-inventory. */
class AdComputer extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'last_logon_at' => 'datetime',
        'ad_created_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function ou()
    {
        return $this->belongsTo(AdOu::class, 'ad_ou_id');
    }
}
