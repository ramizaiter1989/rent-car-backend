<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $car = Car::findOrFail($request->car_id);
        $days = Carbon::parse($request->start_datetime)->diffInDays($request->end_datetime);
        $totalPrice = $car->getTotalPrice($days);

        $booking = Booking::create([
            'client_id' => $request->user()->id,
            'car_id' => $request->car_id,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
            'total_booking_price' => $totalPrice,
            'booking_request_status' => 'pending',
        ]);

        return response()->json(['message' => 'Booking created', 'booking' => $booking], 201);
    }

    public function index(Request $request)
    {
        $bookings = Booking::where('client_id', $request->user()->id)
            ->with(['car', 'feedbacks'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json(['bookings' => $bookings], 200);
    }

    public function show(Request $request, $id)
    {
        $booking = Booking::with(['car', 'client'])->findOrFail($id);

        if ($booking->client_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['booking' => $booking], 200);
    }

    public function cancel(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->client_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $booking->update([
            'booking_request_status' => 'canceled',
            'cancelation_date' => Carbon::now(),
        ]);

        return response()->json(['message' => 'Booking canceled'], 200);
    }

    public function estimate(Request $request, $id)
    {
        $booking = Booking::with('car')->findOrFail($id);
        $days = $booking->getDurationInDays();

        $estimate = [
            'days' => $days,
            'daily_rate' => $booking->car->daily_rate,
            'total' => $booking->car->daily_rate * $days,
        ];

        return response()->json(['estimate' => $estimate], 200);
    }

    public function feedback(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'rating' => 'required|in:1,2,3,4,5',
            'comments' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Feedback::create([
            'client_id' => $request->user()->id,
            'car_id' => $booking->car_id,
            'rating' => $request->rating,
            'comments' => $request->comments,
        ]);

        return response()->json(['message' => 'Feedback submitted'], 200);
    }
}