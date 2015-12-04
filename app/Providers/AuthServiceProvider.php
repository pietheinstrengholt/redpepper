<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        $gate->define('superadmin', function ($user) {
            return $user->role === "superadmin";
        });
		
        $gate->define('admin', function ($user) {
            if ($user->role === "superadmin" || $user->role === "admin") {
				return true;
			}
        });
		
        $gate->define('contributor', function ($user) {
            if ($user->role === "superadmin" || $user->role === "admin" || $user->role === "contributor") {
				return true;
			}
        });
		
        $gate->define('reviewer', function ($user) {
            if ($user->role === "superadmin" || $user->role === "admin" || $user->role === "reviewer") {
				return true;
			}
        });
		
        $gate->define('builder', function ($user) {
            if ($user->role === "superadmin" || $user->role === "admin" || $user->role === "builder") {
				return true;
			}
        });		
		
    }
}
