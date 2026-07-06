<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\WebProgressController;
use App\Http\Controllers\API\AuthController; 

// Form Login Web Tampilan Utama Browser jika belum login
Route::get('/login', [AuthController::class, 'showLoginWeb'])->name('login');
Route::post('/login', [AuthController::class, 'loginWeb']);
Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

// Proteksi Autentikasi untuk CRUD Web
Route::middleware('auth')->group(function () {
    Route::get('/', [WebProgressController::class, 'index']);
    Route::get('/progress/create', [WebProgressController::class, 'create']);
    Route::post('/progress', [WebProgressController::class, 'store']);
    Route::get('/progress/{id}/edit', [WebProgressController::class, 'edit']);
    Route::get('/progress/{id}/preview', [WebProgressController::class, 'preview'])->name('progress.preview');
Route::get('/progress/{id}/file', [WebProgressController::class, 'file'])->name('progress.file');
    Route::post('/progress/{id}/review', [WebProgressController::class, 'review'])->name('progress.review');
    Route::put('/progress/{id}', [WebProgressController::class, 'update']);
    Route::delete('/progress/{id}', [WebProgressController::class, 'destroy']);
});