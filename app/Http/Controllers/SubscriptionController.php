<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    private function normalizePlanTypeAndDuration(Request $request): array
    {
        $rawPlan = $request->input('type', $request->input('plan_type', 'monthly'));
        $duration = (int) $request->input('duration_months', 0);

        $planMap = [
            'monthly' => ['plan_type' => 'basic', 'duration_months' => 1],
            'quarterly' => ['plan_type' => 'premium', 'duration_months' => 3],
            'yearly' => ['plan_type' => 'vip', 'duration_months' => 12],
            'basic' => ['plan_type' => 'basic', 'duration_months' => max($duration, 1)],
            'premium' => ['plan_type' => 'premium', 'duration_months' => max($duration, 1)],
            'vip' => ['plan_type' => 'vip', 'duration_months' => max($duration, 1)],
        ];

        $normalized = $planMap[$rawPlan] ?? ['plan_type' => 'basic', 'duration_months' => max($duration, 1)];

        if ($duration > 0) {
            $normalized['duration_months'] = $duration;
        }

        return $normalized;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Subscription::with(['doctor.user', 'patient.user']);

        // 1. Admin sees everything
        if ($user->isAdmin()) {
            // No additional filtering
        } 
        // 2. Doctor sees only subscriptions related to them
        elseif ($user->isDoctor()) {
            if ($user->doctor) {
                $query->where('doctor_id', $user->doctor->id);
            } else {
                return response()->json([], 200);
            }
        } 
        // 3. Patient sees only their own subscriptions
        elseif ($user->isPatient()) {
            if ($user->patient) {
                $query->where('patient_id', $user->patient->id);
            } else {
                return response()->json([], 200);
            }
        }

        // Additional filter by patient_id if provided (for admins/doctors)
        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $subscriptions = $query->latest()->get()->map(function($sub) {
            $data = $sub->toArray();
            $data['subscription_type'] = $sub->plan_type;
            
            if ($sub->patient && $sub->patient->user) {
                $data['patient_name'] = $sub->patient->user->name;
            } else {
                $data['patient_name'] = 'غير محدد';
            }
            
            if ($sub->doctor) {
                $data['doctor_name'] = $sub->doctor->user ? $sub->doctor->user->name : ($sub->doctor->name ?? 'غير محدد');
            } else {
                $data['doctor_name'] = '-';
            }
            
            return $data;
        });

        return response()->json($subscriptions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'plan_type' => 'sometimes|in:basic,premium,vip,monthly,quarterly,yearly',
            'type' => 'sometimes|in:basic,premium,vip,monthly,quarterly,yearly',
            'price' => 'sometimes|numeric|min:0',
            'duration_months' => 'sometimes|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'sometimes|date',
            'patient_id' => 'sometimes|exists:subscribed_users,id',
            'status' => 'sometimes|in:active,pending,expired,cancelled',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = $request->user();
        
        return \Illuminate\Support\Facades\DB::transaction(function() use ($request, $validated, $user) {
            try {
                // If patient_id is provided (e.g. from Admin Dashboard), use it. Else use current user's patient profile.
                if ($request->has('patient_id') && ($user->isAdmin() || $user->isDoctor())) {
                    $patient = \App\Models\Patient::find($request->patient_id);
                } else {
                    $patient = $user->patient;

                    if (!$patient) {
                        // Create a default patient record if it doesn't exist
                        $patient = \App\Models\Patient::create([
                            'id' => $user->id,
                            'user_id' => $user->id,
                            'fullname' => $request->input('full_name', $user->name),
                            'gender' => $request->input('gender', 'male'),
                            'phone_number' => $request->input('phone', '-'),
                            'birthdate' => $request->input('date_of_birth'),
                            'height' => $request->input('height_cm'),
                            'weight' => $request->input('weight_kg'),
                            'physical_activity' => $request->input('activity'),
                        ]);
                        // Refresh user relationship
                        $user->load('patient');
                    } else {
                        // Update patient with latest info from subscription form
                        $patient->update([
                            'fullname' => $request->input('full_name') ?? $patient->fullname,
                            'gender' => $request->input('gender') ?? $patient->gender ?? 'male',
                            'phone_number' => $request->input('phone') ?? $patient->phone_number,
                            'birthdate' => $request->input('date_of_birth') ?? $patient->birthdate,
                            'height' => $request->input('height_cm') ?? $patient->height,
                            'weight' => $request->input('weight_kg') ?? $patient->weight,
                            'physical_activity' => $request->input('activity') ?? $patient->physical_activity,
                        ]);
                    }
                }

                $normalized = $this->normalizePlanTypeAndDuration($request);
                $status = $request->input('status', 'pending');
                $startDate = $validated['start_date'];
                $durationMonths = $normalized['duration_months'];

                $doctor = \App\Models\Doctor::findOrFail($validated['doctor_id']);
                $resolvedPrice = $validated['price'] ?? $doctor->diet_price;
                if ($resolvedPrice === null) {
                    return response()->json([
                        'message' => 'Price is required. Provide price or set doctor diet_price.'
                    ], 422);
                }
                
                // Calculate end_date based on either duration_months or explicit end_date
                $endDate = $request->input('end_date');
                if (!$endDate) {
                    $endDate = date('Y-m-d', strtotime($startDate . ' + ' . $durationMonths . ' months'));
                }

                $subscription = Subscription::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $validated['doctor_id'],
                    'plan_type' => $normalized['plan_type'],
                    'price' => $resolvedPrice,
                    'duration_months' => $durationMonths,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $status,
                ]);

                if ($patient && $patient->user) {
                    if ($status === 'active') {
                        $patient->user->update(['type' => 'payed']);
                    } elseif ($patient->user->type === 'user') {
                        $patient->user->update(['type' => 'subscribed']);
                    }
                }

                if ($request->hasFile('receipt_image')) {
                    try {
                        $image = $request->file('receipt_image');
                        $imageName = time() . '_' . $image->getClientOriginalName();
                        $destinationPath = public_path('receipts');
                        
                        if (!\Illuminate\Support\Facades\File::exists($destinationPath)) {
                            \Illuminate\Support\Facades\File::makeDirectory($destinationPath, 0755, true);
                        }
                        
                        $image->move($destinationPath, $imageName);
                        $subscription->update(['receipt_image' => 'receipts/' . $imageName]);
                    } catch (\Exception $fileEx) {
                        \Illuminate\Support\Facades\Log::error('Subscription Receipt Upload Failed: ' . $fileEx->getMessage());
                        // We still continue as the subscription record itself is created
                    }
                }

                // Create Invoice
                $subscription->invoices()->create([
                    'invoice_number' => 'INV-' . time(),
                    'amount' => $resolvedPrice,
                    'total_amount' => $resolvedPrice,
                    'due_date' => date('Y-m-d'),
                    'payment_status' => 'pending',
                ]);

                // Notify the doctor
                try {
                    if ($doctor && $doctor->user) {
                        $doctor->user->notify(new \App\Notifications\NewPatientSubscription($patient));
                    }
                } catch (\Exception $notifyEx) {
                    \Illuminate\Support\Facades\Log::warning('Subscription Notification Failed: ' . $notifyEx->getMessage());
                }

                return response()->json($subscription, 201);

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Subscription Store Failed: ' . $e->getMessage());
                throw $e; // Re-throw to trigger rollback
            }
        });
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
            'plan_type' => 'sometimes|in:basic,premium,vip,monthly,quarterly,yearly',
            'type' => 'sometimes|in:basic,premium,vip,monthly,quarterly,yearly',
            'price' => 'sometimes|numeric',
            'duration_months' => 'sometimes|integer|min:1',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'status' => 'sometimes|in:active,expired,cancelled,pending',
            'patient_id' => 'sometimes|exists:subscribed_users,id',
            'doctor_id' => 'sometimes|exists:doctors,id',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->has('type') || $request->has('plan_type') || $request->has('duration_months')) {
            $normalized = $this->normalizePlanTypeAndDuration($request);
            $validated['plan_type'] = $normalized['plan_type'];
            $validated['duration_months'] = $normalized['duration_months'];
            unset($validated['type']);
        }

        if ($request->hasFile('receipt_image')) {
            try {
                // Delete old image if it exists
                if ($subscription->receipt_image) {
                    $oldPath = public_path($subscription->receipt_image);
                    if (\Illuminate\Support\Facades\File::exists($oldPath)) {
                        \Illuminate\Support\Facades\File::delete($oldPath);
                    }
                }

                $image = $request->file('receipt_image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $destinationPath = public_path('receipts');
                
                if (!\Illuminate\Support\Facades\File::exists($destinationPath)) {
                    \Illuminate\Support\Facades\File::makeDirectory($destinationPath, 0755, true);
                }
                
                $image->move($destinationPath, $imageName);
                $validated['receipt_image'] = 'receipts/' . $imageName;
            } catch (\Exception $fileEx) {
                \Illuminate\Support\Facades\Log::error('Subscription Receipt Update Failed: ' . $fileEx->getMessage());
            }
        }

        $subscription->update($validated);
        
        // If subscription is set to active, update the user type
        if (isset($validated['status']) && $validated['status'] === 'active') {
            $patient = $subscription->patient;
            if ($patient && $patient->user) {
                $patient->user->update(['type' => 'payed']);
            }
        }

        return response()->json($subscription->load(['doctor', 'patient']));
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
            'status' => 'required|in:active,inactive,expired,cancelled,pending',
        ]);

        $subscription->update(['status' => $validated['status']]);

        // If subscription is set to active, update the user type
        if ($validated['status'] === 'active') {
            $patient = $subscription->patient;
            if ($patient && $patient->user) {
                $patient->user->update(['type' => 'payed']);
            }
        }

        return response()->json([
            'message' => 'Subscription status updated successfully',
            'subscription' => $subscription->load(['doctor', 'patient'])
        ]);
    }
}
