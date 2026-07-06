<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // ==========================================
    // 1. AUTENTIKASI UTK APLIKASI MOBILE (API)
    // ==========================================

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('mahasiswa.kelas.programStudi')->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Kredensial login salah.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login Berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Berhasil logout dari perangkat mobile']);
    }


    // ==========================================
    // 2. AUTENTIKASI UTK VERSI WEBSITE (BROWSER)
    // ==========================================

    // Menampilkan halaman form login di browser
    public function showLoginWeb()
    {
        return view('auth.login'); // Mengarah ke resources/views/auth/login.blade.php
    }

    // Memproses data dari form login browser web
    public function loginWeb(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Menggunakan sistem Session Guard bawaan Web Laravel
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Jika sukses, lempar ke halaman dashboard utama CRUD
            return redirect()->intended('/');
        }

        // Jika gagal, kembalikan ke halaman login dengan pesan error
        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    // Memproses logout di browser web
    public function logoutWeb(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}