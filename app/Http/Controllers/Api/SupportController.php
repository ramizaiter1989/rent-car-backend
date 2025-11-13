<?php
// ==========================================
// app/Http/Controllers/Api/SupportController.php
// ==========================================
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupportController extends Controller
{
    /**
     * Open support ticket
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'type_of_issue' => 'required|integer',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $appeal = Appeal::create([
            'user_id' => $request->user()->id,
            'booking_id' => $request->booking_id,
            'type_of_issue' => $request->type_of_issue,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Support ticket created',
            'ticket' => $appeal,
        ], 201);
    }

    /**
     * Get user's support tickets
     */
    public function index(Request $request)
    {
        $tickets = Appeal::where('user_id', $request->user()->id)
            ->with('booking')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json(['tickets' => $tickets], 200);
    }

    /**
     * Get ticket details
     */
    public function show(Request $request, $id)
    {
        $ticket = Appeal::with(['user', 'booking.car'])->findOrFail($id);

        if ($ticket->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['ticket' => $ticket], 200);
    }

    /**
     * Add message to ticket
     */
    public function addMessage(Request $request, $id)
    {
        $ticket = Appeal::findOrFail($id);

        if ($ticket->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // TODO: Store message in a separate ticket_messages table
        // For now, append to response field
        $ticket->update([
            'response' => $ticket->response . "\n[" . now() . "] " . $request->message,
        ]);

        return response()->json([
            'message' => 'Message added',
            'ticket' => $ticket,
        ], 200);
    }
}