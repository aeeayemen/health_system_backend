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
        ]);

        $patient = Patient::create($validated);

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
        ]);

        $patient->update($validated);

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
}
