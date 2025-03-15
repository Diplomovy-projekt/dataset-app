<?php

namespace App\Providers;

use App\Models\ActionRequest;
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

        Gate::define('delete-dataset', function ($user, $id) {
            return $user->role === 'admin' || $user->id === Dataset::where('id', $id)
                    ->orWhere('unique_name', $id)
                    ->value('user_id');
        });
        Gate::define('post-dataset', function ($user) {
            return $user !== null;
        });

        Gate::define('update-dataset', function ($user, $id) {
            return $user->role === 'admin' || $user->id === Dataset::where('id', $id)
                    ->orWhere('unique_name', $id)
                    ->value('user_id');
        });

        Gate::define('cancel-request', function ($user, $id) {
            $request = ActionRequest::findOrFail($id);
            return $user->role === 'admin' || $user->id === $request->user_id;
        });
    }
}
