<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\UploadUser;
use App\Models\User;

/**
 * The AuthServiceProvider
 *
 * Provided by Laravel scaffold
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('upload-import', function (User $user) {
            if (!UploadUser::firstWhere('username', $user->username)) {
                return false;
            }

            return true;
        });
    }
}
