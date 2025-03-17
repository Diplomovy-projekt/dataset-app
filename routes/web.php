<?php

use App\Http\Controllers\AnnotationRendererController;
use App\Livewire\Forms\Register;
use App\Livewire\FullPages\AdminDashboard;
use App\Livewire\FullPages\AdminDatasets;
use App\Livewire\FullPages\AdminLogs;
use App\Livewire\FullPages\AdminUsers;
use App\Livewire\FullPages\DatasetBuilder;
use App\Livewire\FullPages\DatasetIndex;
use App\Livewire\FullPages\DatasetShow;
use App\Livewire\FullPages\MyRequests;
use App\Livewire\FullPages\Profile;
use App\Livewire\FullPages\ReviewEditDataset;
use App\Livewire\FullPages\ReviewReduceDataset;
use Illuminate\Support\Facades\File;
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
Route::get('/zip-format-info', function(){
    return view('zip-format-info');
})->name('zip.format.info');
Route::post('/api/annotations', [AnnotationRendererController::class, 'getAnnotations']);

////////////////////////////////////////////////////////////////////////////////
///                     DATASET ROUTES
////////////////////////////////////////////////////////////////////////////////
Route::get('/datasets', DatasetIndex::class)->name('dataset.index');
Route::get('/dataset/{uniqueName}', DatasetShow::class)->name('dataset.show');
Route::get('/builder', DatasetBuilder::class)->name('builder');
// Review mode for admins
Route::get('/dataset/{uniqueName}/review-new/{requestId}', DatasetShow::class)
    ->middleware('admin')
    ->name('dataset.review.new');

Route::get('/dataset/{uniqueName}/review-extend/{requestId}', DatasetShow::class)
    ->middleware('admin')
    ->name('dataset.review.extend');

Route::get('/review-edit/{requestId}', ReviewEditDataset::class)
    ->middleware('admin')
    ->name('dataset.review.edit');

Route::get('/review-reduce/{requestId}', ReviewReduceDataset::class)
    ->middleware('admin')
    ->name('dataset.review.reduce');

Route::get('/dataset/{uniqueName}/review-delete/{requestId}', DatasetShow::class)
    ->middleware('admin')
    ->name('dataset.review.delete');


////////////////////////////////////////////////////////////////////////////////
///                     PROFILE ROUTES
////////////////////////////////////////////////////////////////////////////////
Route::middleware('auth')->group(function () {
    Route::get('/profile', Profile::class)->name('profile');
    Route::get('/my-requests', MyRequests::class)->name('my.requests');
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
    Route::get('admin/logs', AdminLogs::class)->name('admin.logs');
});

Route::get('/private-image/{dataset}/{filename}', function ($dataset, $filename) {
    $filename = base64_decode($filename);

    $datasetRecord = \App\Models\Dataset::where('id', $dataset)
        ->orWhere('unique_name', $dataset)
        ->first();

    if (!auth()->check() || auth()->user()->isAdmin()) {
        //abort(403);
    }
    // Define image path
    $path = storage_path("app/private/datasets/{$datasetRecord->unique_name}/{$filename}");

    // Check if the file exists
    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('filename', '.*')->name('private.image');
