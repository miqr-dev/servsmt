<?php

namespace App\Concerns;

use App\Comment;

/**
 * Add this trait to any model that should support a comment thread
 * (Handwerk, Ticket, Korso, ...).
 *
 * First-party replacement for the abandoned laravelista/comments
 * package's Commentable trait. See config/comments.php,
 * app/Policies/CommentPolicy.php and app/Http/Controllers/CommentController.php
 * for the rest of the port.
 */
trait Commentable
{
    /**
     * Cascade-delete leftover comments once the commentable model is
     * gone for good.
     *
     * Deliberately bound to `forceDeleted`, NOT the vanilla package's
     * `deleted` event: Handwerk/Ticket/Korso all use SoftDeletes, and
     * the `deleted` event fires on every soft-delete too (e.g. "mark
     * ticket as done"). Binding the cascade to `deleted` like the
     * original package did would silently wipe a ticket's entire
     * comment thread every time it's marked done/soft-deleted.
     * `forceDeleted` only fires on a real, permanent delete, which is
     * what a cascade-delete should actually be for.
     */
    protected static function bootCommentable()
    {
        static::forceDeleted(function ($commentable) {
            foreach ($commentable->comments as $comment) {
                $comment->forceDelete();
            }
        });
    }

    /**
     * Returns all comments for this model.
     *
     * withTrashed(): a soft-deleted comment stays visible in the thread
     * (e.g. on a ticket that's since been marked done) instead of
     * disappearing from its history. Nothing currently soft-deletes an
     * individual comment through a live code path - CommentController::
     * destroy() is a no-op - so this is a no-op today, but matches this
     * app's convention of a "done" parent not hiding its child records
     * (see KorsoInternalComment), and is what Korso's own comments()
     * override did before this trait replaced it.
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->withTrashed();
    }

    /**
     * Returns only approved comments for this model.
     */
    public function approvedComments()
    {
        return $this->morphMany(Comment::class, 'commentable')->withTrashed()->where('approved', true);
    }
}
