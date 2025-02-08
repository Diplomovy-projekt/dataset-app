<?php

use App\Http\Controllers\DownloadProgressController;
use App\Livewire\FullPages\DatasetBuilder;
use App\Livewire\FullPages\DatasetIndex;
use App\Livewire\FullPages\DatasetShow;
use App\Livewire\FullPages\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;


//Route::view('dashboard', 'dashboard')
//    ->middleware(['auth', 'verified'])
//    ->name('dashboard');
//
//Route::view('profile', 'profile')
//    ->middleware(['auth'])
//    ->name('profile');

require __DIR__.'/auth.php';
Route::get('/', function () {
    $statistics = \App\Utils\QueryUtil::getDatasetCounts();
    return view('welcome', ['statistics' => $statistics]);
})->name('welcome');
Route::get('/datasets', DatasetIndex::class)->name('dataset.index');
Route::get('/dataset/{uniqueName}', DatasetShow::class)->name('dataset.show');
Route::get('/builder', DatasetBuilder::class)->name('builder');
Route::get('/profile', Profile::class)->name('profile');

Route::get('/download/progress', function () {
    $filePath = request('filePath');
    $progress = Session::get("download_progress_{$filePath}", 0);
    return response()->json(['progress' => $progress]);
});
