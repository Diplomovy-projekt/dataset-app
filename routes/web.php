<?php

use App\Livewire\FullPages\DatasetView;
use App\Livewire\FullPages\Profile;
use App\Livewire\FullPages\Welcome;
use Illuminate\Support\Facades\Route;

Route::get('/', Welcome::class)->name('welcome');

//Route::view('dashboard', 'dashboard')
//    ->middleware(['auth', 'verified'])
//    ->name('dashboard');
//
//Route::view('profile', 'profile')
//    ->middleware(['auth'])
//    ->name('profile');

require __DIR__.'/auth.php';

Route::get('/db-info', function () {
    return config('database.connections.'.config('database.default'));
});

Route::get('/profile', Profile::class)->name('profile');

Route::get('/dataset/{uniqueName}', DatasetView::class)->name('dataset.view');
