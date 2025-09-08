<?php

namespace App\Policies;

use App\InvAbItem;
use Illuminate\Auth\Access\HandlesAuthorization;
use LdapRecord\Models\ActiveDirectory\User;

class InventoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \LdapRecord\Models\ActiveDirectory\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \LdapRecord\Models\ActiveDirectory\User  $user
     * @param  \App\InvAbItem  $invAbItem
     * @return mixed
     */
    public function view(User $user, InvAbItem $invAbItem)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \LdapRecord\Models\ActiveDirectory\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \LdapRecord\Models\ActiveDirectory\User  $user
     * @param  \App\InvAbItem  $invAbItem
     * @return mixed
     */
    public function update(User $user, InvAbItem $invAbItem)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \LdapRecord\Models\ActiveDirectory\User  $user
     * @param  \App\InvAbItem  $invAbItem
     * @return mixed
     */
    public function delete(User $user, InvAbItem $invAbItem)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \LdapRecord\Models\ActiveDirectory\User  $user
     * @param  \App\InvAbItem  $invAbItem
     * @return mixed
     */
    public function restore(User $user, InvAbItem $invAbItem)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \LdapRecord\Models\ActiveDirectory\User  $user
     * @param  \App\InvAbItem  $invAbItem
     * @return mixed
     */
    public function forceDelete(User $user, InvAbItem $invAbItem)
    {
        //
    }
}
