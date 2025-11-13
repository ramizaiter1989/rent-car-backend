<?php

// ==========================================
// app/Http/Controllers/Api/VehicleController.php
// ==========================================
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Search and list vehicles
     */
    public function index(Request $request)
    {
        $query = Car::with(['agent', 'featuredListing'])
            ->where('car_accepted', true)
            ->where('status', 'available');

        // Apply filters
        if ($request->has('make')) {
            $query->where('make', 'like', "%{$request->make}%");
        }

        if ($request->has('model')) {
            $query->where('model', 'like', "%{$request->model}%");
        }

        if ($request->has('category')) {
            $query->where('car_category', $request->category);
        }

        if ($request->has('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        if ($request->has('transmission')) {
            $query->where('transmission', $request->transmission);
        }

        if ($request->has('min_price')) {
            $query->where('daily_rate', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('daily_rate', '<=', $request->max_price);
        }

        if ($request->has('seats')) {
            $query->where('seats', '>=', $request->seats);
        }

        if ($request->has('year')) {
            $query->where('year', '>=', $request->year);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $cars = $query->paginate(20);

        return response()->json(['cars' => $cars], 200);
    }

    /**
     * Get vehicle details
     */
    public function show($id)
    {
        $car = Car::with(['agent', 'feedbacks.client', 'holidays'])
            ->findOrFail($id);

        if (!$car->car_accepted) {
            return response()->json(['error' => 'Vehicle not available'], 404);
        }

        // Increment views
        $car->incrementViews();

        $averageRating = $car->getAverageRating();
        $totalRatings = $car->getTotalRatings();

        return response()->json([
            'car' => $car,
            'average_rating' => $averageRating,
            'total_ratings' => $totalRatings,
        ], 200);
    }
}
