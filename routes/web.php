<?php

use App\Livewire\FullPages\DatasetBuilder;
use App\Livewire\FullPages\DatasetIndex;
use App\Livewire\FullPages\DatasetShow;
use App\Livewire\FullPages\Profile;
use Illuminate\Support\Facades\Route;


//Route::view('dashboard', 'dashboard')
//    ->middleware(['auth', 'verified'])
//    ->name('dashboard');
//
//Route::view('profile', 'profile')
//    ->middleware(['auth'])
//    ->name('profile');

require __DIR__.'/auth.php';
Route::view('/', 'welcome')->name('welcome');
Route::get('/datasets', DatasetIndex::class)->name('dataset.index');
Route::get('/dataset/{uniqueName}', DatasetShow::class)->name('dataset.show');
Route::get('/builder', DatasetBuilder::class)->name('builder');
Route::get('/profile', Profile::class)->name('profile');

