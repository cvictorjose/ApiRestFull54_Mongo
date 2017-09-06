<?php

namespace App\Policies;

use App\User;
use App\Membership;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;


    /**
     * Admin=true can do any tasks
     *
     * @param  \App\User  $user
     * @param  \$ability
     * @return mixed
     */
    public function before (User $user){
        if($user->isAdmin()){
            return true;
        }
    }

    /**
     * Determine whether the user can view the membership.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can create membership.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the membership.
     *
     * @param  \App\User  $user
     * @param  \App\Membership  $membership
     * @return mixed
     */
    public function update(User $user,User $requestUser)
    {
        return $user->_id === $requestUser->_id;

    }

    /**
     * Determine whether the user can delete the membership.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function delete(User $user,User $requestUser)
    {
        return $user->_id === $requestUser->_id;
    }


    /**
     * Determine whether the user can activate the membership.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function activateMembership(User $user)
    {
        return $user->isAdmin();
    }



}
