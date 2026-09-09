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

        // Super_Admin bypasses every Gate::allows()/@can/permission: check.
        // Without this, every permission (role-list, role-create, ...) would
        // need to be individually seeded AND assigned to Super_Admin before
        // that role could use any permission-gated screen - the exact trap
        // RoleController was stuck in (see the roles & permissions audit).
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super_Admin') ? true : null;
        });

        WindowsAuthenticate::rememberAuthenticatedUsers();
        //WindowsAuthenticate::logoutUnauthenticatedUsers();
        //WindowsAuthenticate::bypassDomainVerification();
        //
    }
    
}
