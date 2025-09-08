<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use LdapRecord\Laravel\Middleware\WindowsAuthenticate;
use Laravelista\Comments\Comment;
use Laravelista\Comments\CommentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        // Comment::class => CommentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        WindowsAuthenticate::rememberAuthenticatedUsers();
        //WindowsAuthenticate::logoutUnauthenticatedUsers();
        //WindowsAuthenticate::bypassDomainVerification();
        //
    }
    
}
