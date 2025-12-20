<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return response()->json(Invoice::with(['patient', 'subscription'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'subscription_id' => 'nullable|exists:subscriptions,id',
            'amount' => 'required|numeric',
            'status' => 'required|in:pending,paid,cancelled',
            'due_date' => 'required|date',
        ]);

        $invoice = Invoice::create($validated);

        return response()->json($invoice->load(['patient', 'subscription']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        return response()->json($invoice->load(['patient', 'subscription']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'sometimes|numeric',
            'status' => 'sometimes|in:pending,paid,cancelled',
            'due_date' => 'sometimes|date',
        ]);

        $invoice->update($validated);

        return response()->json($invoice->load(['patient', 'subscription']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully']);
    }

    /**
     * Update invoice payment status.
     */
    public function updateStatus(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $invoice->update(['payment_status' => $validated['payment_status']]);

        return response()->json([
            'message' => 'Invoice status updated successfully',
            'invoice' => $invoice->load(['patient', 'subscription'])
        ]);
    }
}
