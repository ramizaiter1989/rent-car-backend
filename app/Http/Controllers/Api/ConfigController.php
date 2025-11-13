<?php
// ==========================================
// app/Http/Controllers/Api/ConfigController.php
// ==========================================
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ConfigController extends Controller
{
    /**
     * Get app configuration
     */
    public function index()
    {
        $config = [
            'app_name' => config('app.name'),
            'currency' => 'USD',
            'min_booking_hours' => 24,
            'cancellation_policy' => [
                'free_cancellation_hours' => 24,
                'partial_refund_hours' => 12,
                'no_refund_hours' => 0,
            ],
            'fare_structure' => [
                'base_rate' => 50,
                'per_km_rate' => 0.5,
                'per_hour_rate' => 10,
            ],
            'supported_payment_methods' => ['whish', 'omt', 'bank', 'cash'],
            'car_categories' => ['luxury', 'sport', 'commercial', 'industrial', 'normal', 'event', 'sea'],
            'cities' => ['beirut', 'tripoli', 'sidon', 'tyre', 'other'],
        ];

        return response()->json(['config' => $config], 200);
    }
}