<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CmdbController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/cmdb/{categoryId}', [CmdbController::class, 'index'])->name('cmdb.index');
Route::get('/cmdb/{categoryId}/export', [CmdbController::class, 'export'])->name('cmdb.export');
Route::post('/cmdb/{categoryId}/import', [CmdbController::class, 'import'])->name('cmdb.import');
