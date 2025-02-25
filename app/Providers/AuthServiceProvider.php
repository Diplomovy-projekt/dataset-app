<?php

namespace App\Providers;

use App\Models\Dataset;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define('admin', fn($user) => $user->isAdmin());
        Gate::define('user', fn($user) => true);

        Gate::define('delete-dataset', function ($user, $identifier) {
            return $user->role === 'admin' || $user->id === Dataset::where('id', $identifier)
                    ->orWhere('unique_name', $identifier)
                    ->value('user_id');
        });
        Gate::define('post-dataset', function ($user) {
            return $user !== null;
        });

        Gate::define('update-dataset', function ($user, $identifier) {
            return $user->role === 'admin' || $user->id === Dataset::where('id', $identifier)
                    ->orWhere('unique_name', $identifier)
                    ->value('user_id');
        });
    }
}
