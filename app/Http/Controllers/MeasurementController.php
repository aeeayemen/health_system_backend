<?php

namespace App\Http\Controllers;

use App\Models\Measurement;
use App\Models\Patient;
use Illuminate\Http\Request;

class MeasurementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Measurement::with('patient.user')->latest();

        if ($request->user()->isPatient()) {
            $query->where('patient_id', $request->user()->patient->id);
        }

        return response()->json($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'nullable|exists:users,id',
            'weight' => 'required|numeric',
            'waist_circumference' => 'nullable|numeric',
            'hip_circumference' => 'nullable|numeric',
            'chest_circumference' => 'nullable|numeric',
            'measurement_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $patientId = null;
        if ($request->user()->isPatient()) {
            $patientId = $request->user()->patient->id;
        } else {
            // If not a patient (e.g. doctor/admin), patient_id is required
            if (empty($validated['patient_id'])) {
                return response()->json(['message' => 'Patient ID is required'], 422);
            }
            $patientId = $validated['patient_id'];
        }

        $patient = Patient::find($patientId);

        // Calculate BMI
        $bmi = null;
        if ($patient && $patient->height > 0) {
            $heightInMeters = $patient->height / 100;
            $bmi = $validated['weight'] / ($heightInMeters * $heightInMeters);
        }

        $measurement = Measurement::create([
            'patient_id' => $patientId,
            'weight' => $validated['weight'],
            'waist_circumference' => $validated['waist_circumference'] ?? null,
            'hip_circumference' => $validated['hip_circumference'] ?? null,
            'chest_circumference' => $validated['chest_circumference'] ?? null,
            'bmi' => $bmi,
            'measurement_date' => $validated['measurement_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update patient current weight
        if ($patient) {
            $patient->update(['current_weight' => $validated['weight']]);
        }

        return response()->json($measurement, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Measurement $measurement)
    {
        return response()->json($measurement->load('patient.user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Measurement $measurement)
    {
        $validated = $request->validate([
            'weight' => 'required|numeric',
            'waist_circumference' => 'nullable|numeric',
            'hip_circumference' => 'nullable|numeric',
            'chest_circumference' => 'nullable|numeric',
            'measurement_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $patient = $measurement->patient;

        // Recalculate BMI
        $bmi = null;
        if ($patient && $patient->height > 0) {
            $heightInMeters = $patient->height / 100;
            $bmi = $validated['weight'] / ($heightInMeters * $heightInMeters);
        }

        $measurement->update([
            'weight' => $validated['weight'],
            'waist_circumference' => $validated['waist_circumference'] ?? null,
            'hip_circumference' => $validated['hip_circumference'] ?? null,
            'chest_circumference' => $validated['chest_circumference'] ?? null,
            'bmi' => $bmi,
            'measurement_date' => $validated['measurement_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update patient current weight
        if ($patient) {
            $patient->update(['current_weight' => $validated['weight']]);
        }

        return response()->json($measurement);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Measurement $measurement)
    {
        $measurement->delete();
        return response()->json(['message' => 'Measurement deleted']);
    }
}
