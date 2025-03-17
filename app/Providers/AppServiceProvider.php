<?php

namespace App\Providers;

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::addPersistentMiddleware([
            AdminMiddleware::class,
        ]);

        URL::macro('livewireCurrent', function ($absolute = false) {
            if($absolute) {
                if (request()->route()->named('livewire.update')) {
                    return url()->previous();
                } else {
                    return url()->current();
                }
            } else {
                if (request()->route()->named('livewire.update')) {
                    $previousUrl = url()->previous();
                    $previousRoute = app('router')->getRoutes()->match(request()->create($previousUrl));
                    return [
                        'route' => $previousRoute->getName(),
                        'params' => $previousRoute->parameters()
                    ];
                } else {
                    return [
                        'route' => request()->route()->getName(),
                        'params' => request()->route()->parameters()
                    ];
                }
            }
        });

    }
}
