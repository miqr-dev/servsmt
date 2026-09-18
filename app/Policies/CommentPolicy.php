<?php

namespace App\Policies;

use App\Comment;

/**
 * First-party replacement for the abandoned laravelista/comments
 * package's CommentPolicy - ported verbatim (see config/comments.php
 * for the Gate wiring).
 */
class CommentPolicy
{
    /**
     * Can the user create a comment.
     *
     * @param $user
     * @return bool
     */
    public function create($user): bool
    {
        return true;
    }

    /**
     * Can the user delete this comment.
     *
     * @param $user
     * @param Comment $comment
     * @return bool
     */
    public function delete($user, Comment $comment): bool
    {
        return $user->getKey() == $comment->commenter_id;
    }

    /**
     * Can the user update this comment.
     *
     * @param $user
     * @param Comment $comment
     * @return bool
     */
    public function update($user, Comment $comment): bool
    {
        return $user->getKey() == $comment->commenter_id;
    }

    /**
     * Can the user reply to this comment.
     *
     * Deliberately the inverse of update/delete - you can't reply to
     * your own comment, matching the original package's behaviour.
     *
     * @param $user
     * @param Comment $comment
     * @return bool
     */
    public function reply($user, Comment $comment): bool
    {
        return $user->getKey() != $comment->commenter_id;
    }
}
