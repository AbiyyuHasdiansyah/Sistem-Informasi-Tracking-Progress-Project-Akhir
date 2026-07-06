<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProgressController;

// Rute Publik (Akses Tanpa Token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rute Terproteksi (Wajib Membawa Bearer Token dari Flutter)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/profile', function (Request $request) {
        $user = $request->user()->load('mahasiswa.kelas.programStudi');
        $mahasiswa = $user->mahasiswa;

        return response()->json([
            'status' => 'success',
            'user' => $user->toArray(),
            'mahasiswa' => $mahasiswa ? $mahasiswa->toArray() : null,
        ]);
    });
    // Endpoint Tracking Progress Project Akhir
    Route::get('/progress/riwayat', [ProgressController::class, 'index']);
    Route::post('/progress/submit', [ProgressController::class, 'store']);
    Route::get('/progress/{id}/file/{type}', [ProgressController::class, 'file']);
    Route::patch('/progress/{id}', [ProgressController::class, 'update']);
    Route::delete('/progress/{id}', [ProgressController::class, 'destroy']);
    Route::patch('/progress/{id}/review', [ProgressController::class, 'review']);
});