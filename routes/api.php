<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\CarController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Public vehicle routes
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/vehicles/{id}', [VehicleController::class, 'show']);

// Public config
Route::get('/config', [ConfigController::class, 'index']);

// Webhook (no auth)
Route::post('/webhook/payment', [PaymentController::class, 'webhook']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // ==========================================
    // User / Profile / Account
    // ==========================================
    Route::prefix('user')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    Route::prefix('account')->group(function () {
        Route::post('/delete-request', [AuthController::class, 'deleteRequest']);
        Route::delete('/', [AuthController::class, 'deleteAccount']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // ==========================================
    // Booking / Rides (Passenger/Client)
    // ==========================================
    Route::prefix('bookings')->group(function () {
        Route::post('/', [BookingController::class, 'store']);
        Route::get('/', [BookingController::class, 'index']);
        Route::get('/{id}', [BookingController::class, 'show']);
        Route::post('/{id}/cancel', [BookingController::class, 'cancel']);
        Route::post('/{id}/estimate', [BookingController::class, 'estimate']);
        Route::post('/{id}/feedback', [BookingController::class, 'feedback']);
        Route::get('/{id}/invoice', [PaymentController::class, 'getInvoice']);
    });

    // ==========================================
    // Driver / Agent APIs
    // ==========================================
    Route::prefix('driver')->group(function () {
        Route::post('/online', [DriverController::class, 'goOnline']);
        Route::post('/offline', [DriverController::class, 'goOffline']);
        Route::post('/location', [DriverController::class, 'updateLocation']);
        Route::get('/bookings', [DriverController::class, 'getBookings']);
        Route::post('/bookings/{id}/accept', [DriverController::class, 'acceptBooking']);
        Route::post('/bookings/{id}/decline', [DriverController::class, 'declineBooking']);
        Route::post('/bookings/{id}/arrived', [DriverController::class, 'markArrived']);
        Route::post('/bookings/{id}/start', [DriverController::class, 'startRide']);
        Route::post('/bookings/{id}/complete', [DriverController::class, 'completeRide']);
        Route::post('/bookings/{id}/cancel', [DriverController::class, 'cancelBooking']);
        Route::get('/history', [DriverController::class, 'history']);
    });

    // ==========================================
    // Payment & Invoicing
    // ==========================================
    Route::prefix('payments')->group(function () {
        Route::post('/initiate', [PaymentController::class, 'initiate']);
        Route::post('/confirm', [PaymentController::class, 'confirm']);
        Route::post('/refund', [PaymentController::class, 'refund']);
        Route::get('/history', [PaymentController::class, 'history']);
    });

    // ==========================================
    // Notifications & Support
    // ==========================================
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/token', [NotificationController::class, 'registerToken']);
    });

    Route::prefix('support')->group(function () {
        Route::prefix('tickets')->group(function () {
            Route::post('/', [SupportController::class, 'store']);
            Route::get('/', [SupportController::class, 'index']);
            Route::get('/{id}', [SupportController::class, 'show']);
            Route::post('/{id}/message', [SupportController::class, 'addMessage']);
        });
    });

    // ==========================================
    // File Uploads
    // ==========================================
    Route::post('/uploads', [UploadController::class, 'upload']);

    // ==========================================
    // Admin Routes (Admin only)
    // ==========================================
    Route::prefix('admin')->middleware('admin')->group(function () {
        // Users
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/users/{id}', [AdminController::class, 'getUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::delete('/users/{id}/force', [AdminController::class, 'forceDeleteUser']);

        // Bookings
        Route::get('/bookings', [AdminController::class, 'getBookings']);
        Route::get('/bookings/{id}', [AdminController::class, 'getBooking']);
        Route::put('/bookings/{id}', [AdminController::class, 'updateBooking']);
        Route::post('/bookings/{id}/force-complete', [AdminController::class, 'forceCompleteBooking']);

        // Payments
        Route::get('/payments', [AdminController::class, 'getPayments']);
        Route::post('/refunds/{id}', [AdminController::class, 'processRefund']);

        // Reports & Logs
        Route::get('/reports/summary', [AdminController::class, 'getSummary']);
        Route::get('/logs', [AdminController::class, 'getLogs']);
    });


    // ==========================================
// Add these routes to routes/api.php
// ==========================================
    // Cars
    Route::apiResource('cars', CarController::class);
    Route::post('cars/{id}/favorite', [CarController::class, 'toggleFavorite']);
    Route::get('favorites', [CarController::class, 'favorites']);

});