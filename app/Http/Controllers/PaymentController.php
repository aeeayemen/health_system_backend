<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'paymentMethod']);

        // Optional filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'transaction_id' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'status' => 'nullable|in:pending,completed,failed,refunded',
            'payment_date' => 'nullable|date',
        ]);

        // Set defaults
        $validated['status'] = $validated['status'] ?? 'pending';
        $validated['currency'] = $validated['currency'] ?? 'SAR';
        $validated['payment_date'] = $validated['payment_date'] ?? now();

        $payment = Payment::create($validated);

        return response()->json($payment->load(['invoice', 'paymentMethod']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        return response()->json($payment->load(['invoice', 'paymentMethod']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'invoice_id' => 'sometimes|exists:invoices,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'transaction_id' => 'nullable|string',
            'amount' => 'sometimes|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'status' => 'sometimes|in:pending,completed,failed,refunded',
            'payment_date' => 'nullable|date',
        ]);

        $payment->update($validated);

        return response()->json($payment->load(['invoice', 'paymentMethod']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json(['message' => 'Payment deleted successfully']);
    }

    /**
     * Update payment status.
     */
    public function updateStatus(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,completed,failed,refunded',
        ]);

        $payment->update(['status' => $validated['status']]);

        // If payment is completed, update invoice status
        if ($validated['status'] === 'completed' && $payment->invoice) {
            $payment->invoice->update(['payment_status' => 'paid']);
        }

        return response()->json([
            'message' => 'Payment status updated successfully',
            'payment' => $payment->load(['invoice', 'paymentMethod'])
        ]);
    }
}
