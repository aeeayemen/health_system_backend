<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubscribedUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $patients = Patient::with(['user', 'doctor.user'])->get()->map(function($patient) {
            $data = $patient->toArray();
            $data['patient_name'] = $patient->user ? $patient->user->name : $patient->fullname;
            $data['doctor_name'] = $patient->doctor && $patient->doctor->user ? $patient->doctor->user->name : ($patient->doctor->name ?? '-');
            $data['price'] = $patient->subscription_price;
            $data['type'] = $patient->subscription_type;
            $data['start_date'] = $patient->subscription_start_date;
            $data['end_date'] = $patient->subscription_end_date;
            $data['receipt_image'] = $patient->subscription_receipt_image;
            $data['status'] = $patient->subscription_status ?? 'pending';
            return $data;
        });
        return response()->json($patients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Allow user_id to be passed as id, or id itself
        $data = $request->all();
        if (isset($data['user_id']) && !isset($data['id'])) {
            $data['id'] = $data['user_id'];
        }
        $request->merge($data);

        $validated = $request->validate([
            'id' => 'required|exists:users,id|unique:subscribed_users,id',
            'fullname' => 'nullable|string|max:100',
            'gender' => 'nullable|string|max:100',
            'height' => 'nullable|integer',
            'weight' => 'nullable|integer',
            'phone_number' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048', // Validate image file
            'birthdate' => 'nullable|string|max:100',
            'physical_activity' => 'nullable|string|max:100',
            'medical' => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('subscribed_users', 'public');
            $validated['image'] = $path;
        }

        $subscribedUser = Patient::create($validated);

        return response()->json($subscribedUser, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $subscribedUser = Patient::find($id);

        if (!$subscribedUser) {
            return response()->json(['message' => 'Subscribed user not found'], 404);
        }

        return response()->json($subscribedUser);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $subscribedUser = Patient::find($id);

        if (!$subscribedUser) {
            return response()->json(['message' => 'Subscribed user not found'], 404);
        }

        $validated = $request->validate([
            'fullname' => 'nullable|string|max:100',
            'gender' => 'nullable|string|max:100',
            'height' => 'nullable|integer',
            'weight' => 'nullable|integer',
            'phone_number' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
            'birthdate' => 'nullable|string|max:100',
            'physical_activity' => 'nullable|string|max:100',
            'medical' => 'nullable|string|max:100',
            
            // New Subscription fields
            'type' => 'nullable|string|max:100',
            'price' => 'nullable|numeric',
            'doctor_id' => 'nullable|exists:doctors,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,pending,expired,cancelled',
            'receipt_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($subscribedUser->image) {
                Storage::disk('public')->delete($subscribedUser->image);
            }
            $validated['image'] = $request->file('image')->store('subscribed_users', 'public');
        }

        if ($request->hasFile('receipt_image')) {
            if ($subscribedUser->subscription_receipt_image) {
                Storage::disk('public')->delete($subscribedUser->subscription_receipt_image);
            }
            $validated['subscription_receipt_image'] = $request->file('receipt_image')->store('receipts', 'public');
        }

        // Map frontend fields to DB columns
        if (isset($validated['type'])) $validated['subscription_type'] = $validated['type'];
        if (isset($validated['price'])) $validated['subscription_price'] = $validated['price'];
        if (isset($validated['doctor_id'])) $validated['current_doctor_id'] = $validated['doctor_id'];
        if (isset($validated['start_date'])) $validated['subscription_start_date'] = $validated['start_date'];
        if (isset($validated['end_date'])) $validated['subscription_end_date'] = $validated['end_date'];
        if (isset($validated['status'])) {
            $validated['subscription_status'] = $validated['status'];
            if ($validated['status'] === 'active' && $subscribedUser->user) {
                $subscribedUser->user->update(['type' => 'payed']);
            }
        }

        $subscribedUser->update($validated);

        return response()->json($subscribedUser);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subscribedUser = Patient::find($id);

        if (!$subscribedUser) {
            return response()->json(['message' => 'Subscribed user not found'], 404);
        }

        if ($subscribedUser->image) {
            Storage::disk('public')->delete($subscribedUser->image);
        }

        $subscribedUser->delete();

        return response()->json(['message' => 'Subscribed user deleted successfully']);
    }
}
