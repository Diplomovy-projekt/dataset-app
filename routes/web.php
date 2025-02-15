<?php

use App\Livewire\Forms\Register;
use App\Livewire\FullPages\AdminDashboard;
use App\Livewire\FullPages\AdminDatasets;
use App\Livewire\FullPages\AdminUsers;
use App\Livewire\FullPages\DatasetBuilder;
use App\Livewire\FullPages\DatasetIndex;
use App\Livewire\FullPages\DatasetShow;
use App\Livewire\FullPages\Profile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;


//Route::view('dashboard', 'dashboard')
//    ->middleware(['auth', 'verified'])
//    ->name('dashboard');
//
//Route::view('profile', 'profile')
//    ->middleware(['auth'])
//    ->name('profile');

////////////////////////////////////////////////////////////////////////////////
///                     AUTH ROUTES
////////////////////////////////////////////////////////////////////////////////
require __DIR__.'/auth.php';
Route::get('/register/{token}', Register::class)->name('register');
////////////////////////////////////////////////////////////////////////////////
///                     MISC ROUTES
////////////////////////////////////////////////////////////////////////////////
Route::get('/', function () {
    $statistics = \App\Utils\QueryUtil::getDatasetCounts();
    return view('welcome', ['statistics' => $statistics]);
})->name('welcome');
////////////////////////////////////////////////////////////////////////////////
///                     DATASET ROUTES
////////////////////////////////////////////////////////////////////////////////
Route::get('/datasets', DatasetIndex::class)->name('dataset.index');
Route::get('/dataset/{uniqueName}', DatasetShow::class)->name('dataset.show');
Route::get('/builder', DatasetBuilder::class)->name('builder');
////////////////////////////////////////////////////////////////////////////////
///                     PROFILE ROUTES
////////////////////////////////////////////////////////////////////////////////
Route::middleware('auth')->group(function () {
    Route::get('/profile', Profile::class)->name('profile');
    Route::get('/profile/settings', function () {
        return view('profile-settings');
    })->name('profile.settings');
});
////////////////////////////////////////////////////////////////////////////////
///                     ADMIN ROUTES
////////////////////////////////////////////////////////////////////////////////
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin-dashboard', AdminDashboard::class)->name('admin.dashboard');
    Route::get('/admin/users', AdminUsers::class)->name('admin.users');
    Route::get('/admin/datasets', AdminDatasets::class)->name('admin.datasets');
});
