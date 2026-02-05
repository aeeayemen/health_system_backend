<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Http\Resources\PatientResource;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PatientResource::collection(Patient::with(['user', 'doctor'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'current_doctor_id' => 'nullable|exists:doctors,id',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'current_weight' => 'nullable|numeric',
            'target_weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'physical_activity' => 'nullable|string',
        ]);

        // Map frontend keys to database columns
        $data = $validated;
        if (isset($validated['name']))
            $data['fullname'] = $validated['name'];
        if (isset($validated['date_of_birth']))
            $data['birthdate'] = $validated['date_of_birth'];
        if (isset($validated['current_weight']))
            $data['weight'] = $validated['current_weight'];

        // Map medical_history to medical
        if (isset($validated['medical_history'])) {
            $data['medical'] = $validated['medical_history'];
            unset($data['medical_history']);
        }

        // Pass through new fields if they exist
        if (isset($validated['target_weight']))
            $data['target_weight'] = $validated['target_weight'];
        if (isset($validated['allergies']))
            $data['allergies'] = $validated['allergies'];
        if (isset($validated['current_doctor_id']))
            $data['current_doctor_id'] = $validated['current_doctor_id'];

        $patient = Patient::create($data);

        return new PatientResource($patient);
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        return new PatientResource($patient->load(['user', 'doctor']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'current_doctor_id' => 'sometimes|exists:doctors,id',
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female',
            'current_weight' => 'sometimes|numeric',
            'target_weight' => 'sometimes|numeric',
            'height' => 'sometimes|numeric',
            'medical_history' => 'sometimes|string',
            'allergies' => 'sometimes|string',
            'physical_activity' => 'sometimes|string',
        ]);

        // Map frontend keys to database columns
        $data = $validated;
        if (isset($validated['name']))
            $data['fullname'] = $validated['name'];
        if (isset($validated['date_of_birth']))
            $data['birthdate'] = $validated['date_of_birth'];
        if (isset($validated['current_weight']))
            $data['weight'] = $validated['current_weight'];

        // Map medical_history to medical
        if (isset($validated['medical_history'])) {
            $data['medical'] = $validated['medical_history'];
            unset($data['medical_history']);
        }

        // Pass through new fields if they exist
        if (isset($validated['target_weight']))
            $data['target_weight'] = $validated['target_weight'];
        if (isset($validated['allergies']))
            $data['allergies'] = $validated['allergies'];
        if (isset($validated['current_doctor_id']))
            $data['current_doctor_id'] = $validated['current_doctor_id'];

        $patient->update($data);

        return new PatientResource($patient->load(['user', 'doctor']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        $patient->delete();

        return response()->json(['message' => 'Patient deleted successfully']);
    }
    /**
     * Update current patient's profile
     */
    public function updateMyProfile(Request $request)
    {
        $user = $request->user();

        $patient = Patient::where('user_id', $user->id)->first();

        if (!$patient) {
            return response()->json(['message' => 'Patient profile not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female',
            'current_weight' => 'sometimes|numeric',
            'target_weight' => 'sometimes|numeric',
            'height' => 'sometimes|numeric',
            'medical_history' => 'sometimes|string',
            'allergies' => 'sometimes|string',
            'physical_activity' => 'sometimes|string',
        ]);

        // Map frontend keys to database columns
        $data = $validated;
        if (isset($validated['name']))
            $data['fullname'] = $validated['name'];
        if (isset($validated['date_of_birth']))
            $data['birthdate'] = $validated['date_of_birth'];
        if (isset($validated['current_weight']))
            $data['weight'] = $validated['current_weight'];

        // Map medical_history to medical
        if (isset($validated['medical_history'])) {
            $data['medical'] = $validated['medical_history'];
            unset($data['medical_history']);
        }

        // Pass through new fields if they exist
        if (isset($validated['target_weight']))
            $data['target_weight'] = $validated['target_weight'];
        if (isset($validated['allergies']))
            $data['allergies'] = $validated['allergies'];

        $patient->update($data);

        return new PatientResource($patient->load(['user', 'doctor']));
    }
}
