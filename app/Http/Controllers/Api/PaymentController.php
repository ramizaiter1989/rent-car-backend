<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Invoice;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Initiate a payment (create an invoice).
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:100',
            'amount' => 'required|integer',
            'source' => 'required|in:whish,omt,bank,cash',
            'type' => 'required|in:income,expense,debit,upcoming_income',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $invoice = Invoice::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'reference_id' => Str::uuid(), // generate unique reference
            'issue_date' => now(),
            'due_date' => $request->due_date,
            'source' => $request->source,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Invoice created successfully',
            'invoice' => $invoice,
        ]);
    }

    /**
     * Confirm a payment (mark invoice as paid).
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'reference_id' => 'required|exists:invoices,reference_id',
        ]);

        $invoice = Invoice::where('reference_id', $request->reference_id)->firstOrFail();
        $invoice->type = 'income'; // or mark it in a way your app recognizes as paid
        $invoice->save();

        return response()->json([
            'message' => 'Invoice payment confirmed',
            'invoice' => $invoice,
        ]);
    }

    /**
     * Refund a payment (create a negative invoice or mark refunded).
     */
    public function refund(Request $request)
    {
        $request->validate([
            'reference_id' => 'required|exists:invoices,reference_id',
            'description' => 'nullable|string',
        ]);

        $invoice = Invoice::where('reference_id', $request->reference_id)->firstOrFail();

        // Create a refund invoice (negative amount) or mark type as 'expense'
        $refund = Invoice::create([
            'user_id' => $invoice->user_id,
            'name' => 'Refund for ' . $invoice->name,
            'amount' => -$invoice->amount,
            'reference_id' => Str::uuid(),
            'issue_date' => now(),
            'due_date' => now(),
            'source' => $invoice->source,
            'type' => 'expense',
            'description' => $request->description ?? 'Refund',
        ]);

        return response()->json([
            'message' => 'Payment refunded successfully',
            'refund_invoice' => $refund,
        ]);
    }

    /**
     * Get payment history for the authenticated user.
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $invoices = Invoice::where('user_id', $user->id)
            ->orderBy('issue_date', 'desc')
            ->get();

        return response()->json([
            'invoices' => $invoices,
        ]);
    }
}
