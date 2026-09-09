<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permissioncategory extends Model
{
    protected $fillable = ['name'];

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'permissioncategory_id');
    }
}
