<?php

namespace App\Policies;

use App\User;
use App\Comment;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * Admin=true can do any tasks
     *
     * @param  \App\User  $user
     * @param  \$ability
     * @return mixed
     */
    /*public function before (User $user, $ability){
        if($user->isAdmin()){
            return true;
        }
    }*/

    /**
     * Determine whether the user can view the story.
     *
     * @param  \App\User  $user
     * @param  \App\Story  $story
     * @return mixed
     */
    public function view(User $user, Comment $comment)
    {
        return true;
    }

    /**
     * Determine whether the user can create stories.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the story.
     *
     * @param  \App\User  $user
     * @param  \App\Story  $story
     * @return mixed
     */
    public function update(User $user, Comment $comment)
    {

        return $user->_id === $comment->user_id;
    }

    /**
     * Determine whether the user can delete the story.
     *
     * @param  \App\User  $user
     * @param  \App\Story  $story
     * @return mixed
     */
    public function delete(User $user, Comment $comment)
    {
        return $user->_id === $comment->user_id;
    }
}
