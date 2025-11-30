<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view-benchmark', function ($user) {
            return $user->hasPermission('view-benchmark');
        });

        Gate::define('create-benchmark', function ($user) {
            return $user->hasPermission('create-benchmark');
        });

        Gate::define('update-benchmark', function ($user) {
            return $user->hasPermission('update-benchmark');
        });

        Gate::define('delete-benchmark', function ($user) {
            return $user->hasPermission('delete-benchmark');
        });
    }
}