<?php
// ==========================================
// app/Http/Controllers/Api/NotificationController.php
// ==========================================
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Get user notifications
     */
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Mark as read
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['notifications' => $notifications], 200);
    }

    /**
     * Register device token for push notifications
     */
    public function registerToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string',
            'device_type' => 'required|in:ios,android,web',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // TODO: Store device token in a separate table or user metadata

        return response()->json(['message' => 'Device token registered'], 200);
    }
}