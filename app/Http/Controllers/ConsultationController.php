<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Consultation::with(['doctor', 'patient']);

        if ($request->user()->isPatient()) {
            $query->where('patient_id', $request->user()->patient->id);
        } elseif ($request->user()->isDoctor()) {
            $query->where('doctor_id', $request->user()->doctor->id);
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
            'consultation_type' => 'required|in:initial,follow_up,review',
            'scheduled_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $consultation = Consultation::create([
            'doctor_id' => $validated['doctor_id'],
            'patient_id' => $request->user()->patient->id,
            'consultation_type' => $validated['consultation_type'],
            'scheduled_date' => $validated['scheduled_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json($consultation, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Consultation $consultation)
    {
        $validated = $request->validate([
            'status' => 'in:pending,completed,cancelled',
            'notes' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'whatsapp_link' => 'nullable|string',
        ]);

        $consultation->update($validated);

        return response()->json($consultation);
    }
}
