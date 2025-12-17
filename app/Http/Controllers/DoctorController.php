<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Http\Resources\DoctorResource;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Doctor::with('user');

        if ($request->has('application_status')) {
            $query->where('application_status', $request->application_status);
        } elseif ($request->has('status')) {
            $query->where('application_status', $request->status);
        }

        $doctors = $query->get();
        return DoctorResource::collection($doctors);
    }

    public function approvedApplications()
    {
        $doctors = Doctor::with('user')
            ->where('application_status', 'approved')
            ->get();

        return DoctorResource::collection($doctors);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'specialization' => 'required|string',
            'license_number' => 'required|string|unique:doctors',
            'years_of_experience' => 'nullable|integer',
            'bio' => 'nullable|string',
            'consultation_fee' => 'nullable|numeric',
            'gender' => 'nullable|string',
            'degree' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'CV' => 'nullable|string',
            'profile_image' => 'nullable|string',
            'admin_id' => 'nullable|exists:users,id',
        ]);

        $doctor = Doctor::create($validated);

        return new DoctorResource($doctor);
    }

    /**
     * Display the specified resource.
     */
    public function show(Doctor $doctor)
    {
        return new DoctorResource($doctor->load('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'specialization' => 'sometimes|string',
            'bio' => 'nullable|string',
            'consultation_fee' => 'nullable|numeric',
            'is_available' => 'sometimes|boolean',
            'years_of_experience' => 'nullable|integer',
            'license_number' => 'sometimes|string',
            'gender' => 'nullable|string',
            'degree' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'CV' => 'nullable|string',
            'profile_image' => 'nullable|string',
            'admin_id' => 'nullable|exists:users,id',
        ]);

        $doctor->update($validated);

        return new DoctorResource($doctor);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return response()->json(['message' => 'Doctor deleted successfully']);
    }

    /**
     * Get all pending doctor applications
     */
    public function pendingApplications()
    {
        $doctors = Doctor::with('user')
            ->where('application_status', 'pending')
            ->get();

        return DoctorResource::collection($doctors);
    }

    /**
     * Approve a doctor application
     */
    public function approveApplication(Doctor $doctor)
    {
        $doctor->update([
            'application_status' => 'approved',
            'is_verified' => true,
            'verification_date' => now()
        ]);

        return response()->json([
            'message' => 'Doctor application approved successfully',
            'doctor' => new DoctorResource($doctor->load('user'))
        ]);
    }

    /**
     * Reject a doctor application
     */
    public function rejectApplication(Doctor $doctor)
    {
        $doctor->update([
            'application_status' => 'rejected'
        ]);

        return response()->json([
            'message' => 'Doctor application rejected successfully',
            'doctor' => new DoctorResource($doctor->load('user'))
        ]);
    }
    public function updateStatus(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $status = $request->input('status');
        if ($status === 'approved') {
            return $this->approveApplication($doctor);
        } elseif ($status === 'rejected') {
            return $this->rejectApplication($doctor);
        }
        return response()->json(['message' => 'Invalid status'], 400);
    }
}
