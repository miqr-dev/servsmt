<?php

namespace App;

use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Extends Spatie's own Permission model (rather than a bare Eloquent Model,
 * which is what this class used to be) so it keeps all real Spatie behaviour
 * (guard handling, role relations, etc.) while adding the category()
 * relation that roles.create / roles.edit already expected via
 * Permission::with('category') but that never existed anywhere.
 *
 * Registered as the active permission model in config/permission.php.
 */
class Permission extends SpatiePermission
{
    public function category()
    {
        return $this->belongsTo(Permissioncategory::class, 'permissioncategory_id');
    }
}
