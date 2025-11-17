<?php

namespace App\Providers;

use App\Contracts\UserRepositoryInterface;
use App\Repositories\EloquentUserRepository;
use App\Repositories\RedisUserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        $this->app->extend(UserRepositoryInterface::class, function ($repository, $app) {
            return new RedisUserRepository($repository);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
