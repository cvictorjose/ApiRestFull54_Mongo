<?php

namespace App\Policies;

use App\User;
use App\Genre;
use Illuminate\Auth\Access\HandlesAuthorization;

class GenrePolicy
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
     * Determine whether the user can view the genre.
     *
     * @param  \App\User  $user
     * @param  \App\Genre  $genre
     * @return mixed
     */
    public function view(User $user, Genre $genre)
    {
        return true;
    }

    /**
     * Determine whether the user can create genre.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the genre.
     *
     * @param  \App\User  $user
     * @param  \App\Genre  $genre
     * @return mixed
     */
    public function update(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the genre.
     *
     * @param  \App\User  $user
     * @param  \App\Genre  $genre
     * @return mixed
     */
    public function delete(User $user, Genre $genre)
    {
        return $user->isAdmin();
    }
}
