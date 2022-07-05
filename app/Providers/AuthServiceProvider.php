<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
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

        if (! $this->app->routesAreCached()) {
            Passport::routes();

            Passport::tokensCan([
                'Admin' => 'Add/Edit/Delete Users',
                'Operator' => 'Add/Edit Users',
                'User' => 'List Users'
            ]);
        
            Passport::setDefaultScope([
                'User'
            ]);

            Passport::personalAccessTokensExpireIn(Carbon::now()->addHours(1));
        }
    }
}
