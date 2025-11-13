<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    /**
     * Get all cars (agent's cars)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // If agent, show their cars
        if (in_array($user->role, ['agency', 'employee'])) {
            $cars = Car::where('agent_id', $user->id)
                ->with(['bookings', 'featuredListing'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } 
        // If admin, show all cars
        elseif ($user->role === 'admin') {
            $cars = Car::with(['agent', 'bookings', 'featuredListing'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }
        // If client, show available cars
        else {
            $cars = Car::where('car_accepted', true)
                ->where('status', 'available')
                ->with(['agent', 'feedbacks'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return response()->json(['cars' => $cars], 200);
    }

    /**
     * Create new car (agent only)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['agency', 'employee', 'admin'])) {
            return response()->json(['error' => 'Unauthorized. Only agents can add cars.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'license_plate' => 'required|string|max:50|unique:cars,license_plate',
            'fuel_type' => 'required|in:gasoline,diesel,electric,hybrid',
            'transmission' => 'required|in:automatic,manual,semi-automatic',
            'car_category' => 'required|in:luxury,sport,commercial,industrial,normal,event,sea',
            'daily_rate' => 'required|numeric|min:0',
            'seats' => 'required|integer|min:1|max:50',
            'doors' => 'required|integer|min:1|max:10',
            
            // Optional fields
            'cylinder_number' => 'nullable|in:4,6,8',
            'color' => 'nullable|string|max:100',
            'mileage' => 'nullable|integer|min:0',
            'wheels_drive' => 'nullable|in:4x4,2_front,2_back,autoblock',
            'features' => 'nullable|array',
            'holiday_rate' => 'nullable|numeric|min:0',
            'is_deposit' => 'nullable|boolean',
            'deposit' => 'nullable|numeric|min:0',
            'is_delivered' => 'nullable|boolean',
            'delivery_fees' => 'nullable|numeric|min:0',
            'with_driver' => 'nullable|boolean',
            'driver_fees' => 'nullable|numeric|min:0',
            'max_driving_mileage' => 'nullable|integer|min:0',
            'min_renting_days' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            
            // Images
            'main_image_url' => 'nullable|string',
            'front_image_url' => 'nullable|string',
            'back_image_url' => 'nullable|string',
            'left_image_url' => 'nullable|string',
            'right_image_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $car = Car::create([
            'agent_id' => $user->id,
            'make' => $request->make,
            'model' => $request->model,
            'year' => $request->year,
            'license_plate' => $request->license_plate,
            'fuel_type' => $request->fuel_type,
            'transmission' => $request->transmission,
            'car_category' => $request->car_category,
            'daily_rate' => $request->daily_rate,
            'seats' => $request->seats,
            'doors' => $request->doors,
            'cylinder_number' => $request->cylinder_number,
            'color' => $request->color,
            'mileage' => $request->mileage ?? 0,
            'wheels_drive' => $request->wheels_drive,
            'features' => $request->features,
            'holiday_rate' => $request->holiday_rate,
            'is_deposit' => $request->is_deposit ?? false,
            'deposit' => $request->deposit ?? 0,
            'is_delivered' => $request->is_delivered ?? false,
            'delivery_fees' => $request->delivery_fees,
            'with_driver' => $request->with_driver ?? false,
            'driver_fees' => $request->driver_fees,
            'max_driving_mileage' => $request->max_driving_mileage,
            'min_renting_days' => $request->min_renting_days ?? 1,
            'notes' => $request->notes,
            'main_image_url' => $request->main_image_url,
            'front_image_url' => $request->front_image_url,
            'back_image_url' => $request->back_image_url,
            'left_image_url' => $request->left_image_url,
            'right_image_url' => $request->right_image_url,
            'status' => 'available',
            'car_accepted' => false, // Needs admin approval
        ]);

        return response()->json([
            'message' => 'Car created successfully. Pending admin approval.',
            'car' => $car,
        ], 201);
    }

    /**
     * Get car details
     */
    public function show(Request $request, $id)
    {
        $car = Car::with(['agent', 'bookings', 'feedbacks.client', 'holidays'])
            ->findOrFail($id);

        // Check authorization
        $user = $request->user();
        if (!$car->car_accepted && 
            $car->agent_id !== $user->id && 
            $user->role !== 'admin') {
            return response()->json(['error' => 'Car not found'], 404);
        }

        return response()->json([
            'car' => $car,
            'average_rating' => $car->getAverageRating(),
            'total_ratings' => $car->getTotalRatings(),
        ], 200);
    }

    /**
     * Update car
     */
    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);
        $user = $request->user();

        // Check authorization
        if ($car->agent_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'daily_rate' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:available,not_available,rented,maintenance',
            'mileage' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $car->update($request->only([
            'make', 'model', 'year', 'daily_rate', 'status', 
            'mileage', 'notes', 'color', 'holiday_rate',
            'delivery_fees', 'driver_fees', 'min_renting_days'
        ]));

        return response()->json([
            'message' => 'Car updated successfully',
            'car' => $car,
        ], 200);
    }

    /**
     * Delete car
     */
    public function destroy(Request $request, $id)
    {
        $car = Car::findOrFail($id);
        $user = $request->user();

        // Check authorization
        if ($car->agent_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if car has active bookings
        if ($car->activeBookings()->exists()) {
            return response()->json([
                'error' => 'Cannot delete car with active bookings'
            ], 400);
        }

        $car->delete();

        return response()->json(['message' => 'Car deleted successfully'], 200);
    }

    /**
     * Toggle favorite
     */
    public function toggleFavorite(Request $request, $id)
    {
        $car = Car::findOrFail($id);
        $user = $request->user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('car_id', $car->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $message = 'Removed from favorites';
            $isFavorite = false;
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'car_id' => $car->id,
            ]);
            $message = 'Added to favorites';
            $isFavorite = true;
        }

        return response()->json([
            'message' => $message,
            'is_favorite' => $isFavorite,
        ], 200);
    }

    /**
     * Get user's favorite cars
     */
    public function favorites(Request $request)
    {
        $favorites = Car::whereHas('favorites', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->with(['agent', 'feedbacks'])
            ->paginate(20);

        return response()->json(['favorites' => $favorites], 200);
    }
}

