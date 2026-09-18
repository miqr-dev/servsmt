<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // First-party replacement for the abandoned laravelista/comments
        // package - see config/comments.php and app/Policies/CommentPolicy.php.

        // Register the comment permission Gates (create-comment,
        // delete-comment, edit-comment, reply-to-comment) from config,
        // same as the old package's ServiceProvider did.
        foreach (Config::get('comments.permissions', []) as $permission => $policy) {
            Gate::define($permission, $policy);
        }

        // The vendor/laravelista/comments Blade partials (already present
        // under resources/views/vendor/comments) reference their own views
        // via the "comments::" namespace, e.g. @include('comments::_comment', ...).
        $this->loadViewsFrom(resource_path('views/vendor/comments'), 'comments');

        // Register the @comments(['model' => $handwerk]) Blade directive
        // used by handwerk/show, korso/show, and the ticket detail views.
        Blade::include('vendor.comments.components.comments', 'comments');
    }
}
