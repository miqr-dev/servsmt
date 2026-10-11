<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A device in an AD / app room that is neither in AD nor in the old inventory,
 * added by a user in a ticket form or by IT (see App\Support\AdDevices).
 */
class AdDevice extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['checked_at' => 'datetime'];

    public function room()
    {
        return $this->belongsTo(AdOu::class, 'ad_ou_id')->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
