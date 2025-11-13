<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 🌐 Welcome Page
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// 🧑‍💻 Guest Routes (only for unauthenticated users)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Register
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Forgot password (optional)
    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
});

// 🔐 Authenticated Routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // General Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Dashboard
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        // Add more admin-specific routes here
    });

    // Agency Dashboard
    Route::middleware('role:agency')->prefix('agent')->name('agent.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'agentDashboard'])->name('dashboard');
    });

    // Client Dashboard
    Route::middleware('role:client')->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'clientDashboard'])->name('dashboard');
    });
});
