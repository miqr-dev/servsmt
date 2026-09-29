<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Termination extends Model
{
  use SoftDeletes;
  protected $guarded = [];
  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    // Serialized as plain Y-m-d: a datetime cast would send e.g.
    // 2026-09-10T22:00:00Z (UTC) for the 11th and shift the day in the UI.
    'exit' => 'date:Y-m-d',
    'is_active' => 'boolean',
    'removed_at' => 'datetime',
  ];

  /** Why an entry was "Entfernt" (not deleted) - key => label. */
  public const REMOVAL_REASONS = [
    'withdrawn' => 'Kündigung zurückgezogen',
    'renewed'   => 'Vertrag verlängert',
    'other'     => 'Sonstiges',
  ];

  public function removedByUser()
  {
    return $this->belongsTo(User::class, 'removed_by')->withTrashed();
  }
}
