<?php
// ==========================================
// app/Http/Controllers/Api/AdminController.php
// ==========================================
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Middleware\AdminMiddleware;


class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return $next($request);
        });
    }

    /**
     * Get all users
     */
    public function getUsers(Request $request)
    {
        $query = User::with(['client', 'agent']);

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status === 'active');
        }

        $users = $query->paginate(50);

        return response()->json(['users' => $users], 200);
    }

    /**
     * Get user details
     */
    public function getUser($id)
    {
        $user = User::with(['client', 'agent', 'bookings', 'cars', 'invoices'])
            ->findOrFail($id);

        return response()->json(['user' => $user], 200);
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'verified_by_admin' => 'nullable|boolean',
            'status' => 'nullable|boolean',
            'is_locked' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only(['verified_by_admin', 'status', 'is_locked']));

        return response()->json([
            'message' => 'User updated',
            'user' => $user,
        ], 200);
    }

    /**
     * Soft delete user
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => false, 'is_locked' => true]);

        return response()->json(['message' => 'User deactivated'], 200);
    }

    /**
     * Permanently delete user
     */
    public function forceDeleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User permanently deleted'], 200);
    }

    /**
     * Get all bookings
     */
    public function getBookings(Request $request)
    {
        $query = Booking::with(['client', 'car.agent']);

        if ($request->has('status')) {
            $query->where('booking_request_status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json(['bookings' => $bookings], 200);
    }

    /**
     * Get booking details
     */
    public function getBooking($id)
    {
        $booking = Booking::with(['client', 'car.agent', 'checkPhotos', 'feedbacks'])
            ->findOrFail($id);

        return response()->json(['booking' => $booking], 200);
    }

    /**
     * Update booking
     */
    public function updateBooking(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'booking_request_status' => 'nullable|in:pending,approved,not_approved,canceled',
            'total_booking_price' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $booking->update($request->only(['booking_request_status', 'total_booking_price']));

        return response()->json([
            'message' => 'Booking updated',
            'booking' => $booking,
        ], 200);
    }

    /**
     * Force complete booking
     */
    public function forceCompleteBooking($id)
    {
        $booking = Booking::with('car')->findOrFail($id);

        $booking->update(['booking_request_status' => 'approved']);
        $booking->car->update(['status' => 'available']);

        return response()->json([
            'message' => 'Booking force completed',
            'booking' => $booking,
        ], 200);
    }

    /**
     * Get all payments
     */
    public function getPayments(Request $request)
    {
        $payments = Invoice::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json(['payments' => $payments], 200);
    }

    /**
     * Process refund (admin)
     */
    public function processRefund($id)
    {
        // This uses the PaymentController's refund method
        return app(PaymentController::class)->refund(request()->merge(['invoice_id' => $id]));
    }

    /**
     * Get summary reports
     */
    public function getSummary()
    {
        $summary = [
            'total_users' => User::count(),
            'active_users' => User::where('status', true)->count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('booking_request_status', 'pending')->count(),
            'approved_bookings' => Booking::where('booking_request_status', 'approved')->count(),
            'total_revenue' => Invoice::where('type', 'income')->sum('amount'),
            'total_refunds' => Invoice::where('type', 'expense')->sum('amount'),
            'total_cars' => \App\Models\Car::count(),
            'available_cars' => \App\Models\Car::where('status', 'available')->count(),
        ];

        return response()->json(['summary' => $summary], 200);
    }

    /**
     * Get system logs (mock)
     */
    public function getLogs(Request $request)
    {
        // TODO: Implement proper logging system
        // For now, return recent activities
        
        $logs = DB::table('bookings')
            ->select('id', 'created_at', 'booking_request_status')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json(['logs' => $logs], 200);
    }
}