<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DriverController extends Controller
{
    public function goOnline(Request $request)
    {
        $request->user()->update(['status' => true]);
        return response()->json(['message' => 'Now online'], 200);
    }

    public function goOffline(Request $request)
    {
        $request->user()->update(['status' => false]);
        return response()->json(['message' => 'Now offline'], 200);
    }

    public function updateLocation(Request $request)
    {
        $car = Car::findOrFail($request->car_id);
        $car->update([
            'live_location' => ['lat' => $request->latitude, 'lng' => $request->longitude],
        ]);
        return response()->json(['message' => 'Location updated'], 200);
    }

    public function getBookings(Request $request)
    {
        $bookings = Booking::whereHas('car', function ($q) use ($request) {
                $q->where('agent_id', $request->user()->id);
            })
            ->whereIn('booking_request_status', ['pending', 'approved'])
            ->with(['car', 'client'])
            ->paginate(20);

        return response()->json(['bookings' => $bookings], 200);
    }

    public function acceptBooking(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['booking_request_status' => 'approved']);
        return response()->json(['message' => 'Booking accepted'], 200);
    }

    public function declineBooking(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['booking_request_status' => 'not_approved']);
        return response()->json(['message' => 'Booking declined'], 200);
    }

    public function markArrived(Request $request, $id)
    {
        return response()->json(['message' => 'Marked arrived'], 200);
    }

    public function startRide(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->car->update(['status' => 'rented']);
        return response()->json(['message' => 'Ride started'], 200);
    }

    public function completeRide(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->car->update(['status' => 'available']);
        return response()->json(['message' => 'Ride completed'], 200);
    }

    public function cancelBooking(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['booking_request_status' => 'canceled']);
        return response()->json(['message' => 'Booking canceled'], 200);
    }

    public function history(Request $request)
    {
        $bookings = Booking::whereHas('car', function ($q) use ($request) {
                $q->where('agent_id', $request->user()->id);
            })
            ->with(['car', 'client'])
            ->paginate(20);

        return response()->json(['bookings' => $bookings], 200);
    }
}