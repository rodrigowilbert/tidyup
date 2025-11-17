<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Providers\RepositoryServiceProvider;
use App\Providers\ServiceServiceProvider;
use App\Models\User;
use App\Observers\UserObserver;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(RepositoryServiceProvider::class);
        $this->app->register(ServiceServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
