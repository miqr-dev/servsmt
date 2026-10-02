<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * One profile -> Active Directory write-back (see config/ad_writeback.php).
 */
class AdSyncLog extends Model
{
    public const PENDING = 'pending';
    public const SUCCESS = 'success';
    public const FAILED = 'failed';
    public const NOT_FOUND = 'not_found';

    protected $guarded = [];

    protected $casts = [
        'changes' => 'array',
        'synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by')->withTrashed();
    }
}
