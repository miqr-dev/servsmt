<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Handwerk-Zuständigkeiten of one user (see App\Support\HandwerkResponsibility).
 */
class HandwerkUserSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'view_cities' => 'array',
        'assign_cities' => 'array',
        'pdf_by_mail' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
