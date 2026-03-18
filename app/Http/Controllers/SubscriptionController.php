<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Subscription::with(['doctor', 'patient']);

        if ($request->user()->isPatient()) {
            $query->where('patient_id', $request->user()->patient->id);
        } elseif ($request->user()->isDoctor()) {
            $query->where('doctor_id', $request->user()->doctor->id);
        } elseif ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        return response()->json($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'plan_type' => 'required|in:basic,premium,vip',
            'price' => 'required|numeric',
            'duration_months' => 'required|integer|min:1',
            'start_date' => 'required|date',
        ]);

        $user = $request->user();
        $patient = $user->patient;

        if (!$patient) {
            // Create a default patient record if it doesn't exist
            $patient = \App\Models\Patient::create([
                'id' => $user->id,
                'user_id' => $user->id,
                'fullname' => $user->name,
                // Add other default fields if necessary, but they are nullable in migration
            ]);
            // Refresh user relationship
            $user->load('patient');
        }

        $subscription = Subscription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $validated['doctor_id'],
            'plan_type' => $validated['plan_type'],
            'price' => $validated['price'],
            'duration_months' => $validated['duration_months'],
            'start_date' => $validated['start_date'],
            'end_date' => date('Y-m-d', strtotime($validated['start_date'] . ' + ' . $validated['duration_months'] . ' months')),
            'status' => 'active',
        ]);

        // Create Invoice logic here (simplified)
        $subscription->invoices()->create([
            'invoice_number' => 'INV-' . time(),
            'amount' => $validated['price'],
            'total_amount' => $validated['price'],
            'due_date' => date('Y-m-d'),
            'payment_status' => 'pending',
        ]);

        // Notify the doctor about new patient subscription
        $doctor = \App\Models\Doctor::find($validated['doctor_id']);
        if ($doctor && $doctor->user) {
            $doctor->user->notify(new \App\Notifications\NewPatientSubscription($patient));
        }

        return response()->json($subscription, 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription)
    {
        return response()->json($subscription->load(['doctor', 'patient']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'plan_type' => 'sometimes|in:basic,premium,vip',
            'price' => 'sometimes|numeric',
            'duration_months' => 'sometimes|integer|min:1',
            'start_date' => 'sometimes|date',
            'status' => 'sometimes|in:active,expired,cancelled',
        ]);

        $subscription->update($validated);

        return response()->json($subscription);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return response()->json(['message' => 'Subscription deleted successfully']);
    }

    /**
     * Update subscription status.
     */
    public function updateStatus(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:active,inactive,expired,cancelled',
        ]);

        $subscription->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Subscription status updated successfully',
            'subscription' => $subscription->load(['doctor', 'patient'])
        ]);
    }
}
