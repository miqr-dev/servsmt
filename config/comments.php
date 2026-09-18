<?php

return [

    /*
     * The comment model used to store and retrieve comments.
     */
    'model' => \App\Comment::class,

    /*
     * Gate definitions for comment permissions - registered in
     * AppServiceProvider::boot(). Points at this app's own first-party
     * CommentPolicy (App\Policies\CommentPolicy), not the abandoned
     * laravelista/comments package's policy class.
     */
    'permissions' => [
        'create-comment' => 'App\Policies\CommentPolicy@create',
        'delete-comment' => 'App\Policies\CommentPolicy@delete',
        'edit-comment' => 'App\Policies\CommentPolicy@update',
        'reply-to-comment' => 'App\Policies\CommentPolicy@reply',
    ],

    /**
     * The Comment Controller.
     * Change this to your own implementation of the CommentController.
     */
    'controller' => 'App\Http\Controllers\CommentController',

    /*
     * If true, new comments need `approved` set to true (e.g. by an
     * admin) before they're considered approved. This app posts
     * comments as approved immediately, matching original behaviour.
     */
    'approval_required' => false,

    /*
     * If true, guests (not logged in) may post comments by supplying
     * a name/email. This app requires authentication to comment.
     */
    'guest_commenting' => false,

    /*
     * Whether CommentController::destroy() should soft-delete instead
     * of force-deleting. destroy() is currently a no-op (deleting
     * comments is disabled in the UI), so this has no effect yet, but
     * is kept for parity with the original config.
     */
    'soft_deletes' => false,

];
