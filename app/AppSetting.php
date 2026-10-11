<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Key/value settings changed in the app (table app_settings, 2026-10-11).
 * See App\Support\EmployeePassword for the first one.
 */
class AppSetting extends Model
{
    protected $guarded = [];

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }
}
