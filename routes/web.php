<?php

use App\Livewire\HelloWorld;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

Route::get('/db-info', function () {
    return config('database.connections.'.config('database.default'));
});

Route::get('/hello-world', HelloWorld::class);
