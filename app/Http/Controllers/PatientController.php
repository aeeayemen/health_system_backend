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
        return PatientResource::collection(Patient::with(['user', 'doctor', 'subscriptions.doctor'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'physical_activity' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/patients/profile'), $imageName);
            $validated['image'] = 'uploads/patients/profile/' . $imageName;
        }

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
        if (isset($validated['image']))
            $data['image'] = $validated['image'];

        $patient = Patient::create($data);

        return new PatientResource($patient);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $patient = Patient::where('id', $id)->first();
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }
        return new PatientResource($patient->load(['user', 'doctor', 'subscriptions.doctor']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
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

        $patient = Patient::where('id', $id)->first();
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

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

        return new PatientResource($patient->load(['user', 'doctor', 'subscriptions.doctor']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $patient = Patient::where('id', $id)->first();
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }
        $patient->delete();

        return response()->json(['message' => 'Patient deleted successfully']);
    }
    /**
     * Get current patient's profile
     */
    public function myProfile(Request $request)
    {
        $user = $request->user();
        $patient = Patient::where('user_id', $user->id)->first();

        if (!$patient) {
            // Auto-create profile if missing
            $patient = Patient::create([
                'id' => $user->id,
                'user_id' => $user->id,
                'fullname' => $user->name,
            ]);
        }

        return new PatientResource($patient->load(['user', 'doctor', 'subscriptions.doctor']));
    }

    /**
     * Update current patient's profile
     */
    public function updateMyProfile(Request $request)
    {
        $user = $request->user();
        $patient = Patient::where('user_id', $user->id)->first();

        if (!$patient) {
            $patient = Patient::create([
                'id' => $user->id,
                'user_id' => $user->id,
                'fullname' => $user->name,
            ]);
        }

        // Robust input handling: check for root, data wrapper, or JSON string 'data'
        $rawInput = $request->all();
        if ($request->has('data')) {
            $dataWrapper = $request->input('data');
            if (is_string($dataWrapper)) {
                $dataWrapper = json_decode($dataWrapper, true) ?? [];
            }
            if (is_array($dataWrapper)) {
                $rawInput = array_merge($rawInput, $dataWrapper);
            }
        }

        $validated = \Validator::make($rawInput, [
            'name' => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|nullable|date',
            'birthdate' => 'sometimes|nullable|date',
            'age' => 'sometimes|nullable|integer|min:1|max:120',
            'gender' => 'sometimes|nullable|in:male,female',
            'current_weight' => 'sometimes|nullable|numeric',
            'weight' => 'sometimes|nullable|numeric',
            'target_weight' => 'sometimes|nullable|numeric',
            'height' => 'sometimes|nullable|numeric',
            'medical_history' => 'sometimes|nullable|string',
            'allergies' => 'sometimes|nullable|string',
            'physical_activity' => 'sometimes|nullable|string',
            'current_doctor_id' => 'sometimes|nullable|exists:doctors,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ])->validate();

        $data = $validated;

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($patient->image && file_exists(public_path($patient->image))) {
                @unlink(public_path($patient->image));
            }
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/patients/profile'), $imageName);
            $data['image'] = 'uploads/patients/profile/' . $imageName;
        }

        // Explicit mappings to database columns
        if (isset($validated['name'])) {
            $data['fullname'] = $validated['name'];
        }

        if (isset($validated['date_of_birth'])) {
            $data['birthdate'] = $validated['date_of_birth'];
        } elseif (isset($validated['birthdate'])) {
            $data['birthdate'] = $validated['birthdate'];
        } elseif (isset($validated['age'])) {
            $data['birthdate'] = \Carbon\Carbon::now()->subYears($validated['age'])->format('Y-01-01');
        }

        if (isset($validated['current_weight'])) {
            $data['weight'] = $validated['current_weight'];
        } elseif (isset($validated['weight'])) {
            $data['weight'] = $validated['weight'];
        }

        if (isset($validated['medical_history'])) {
            $data['medical'] = $validated['medical_history'];
            unset($data['medical_history']);
        }

        $patient->update($data);

        return new PatientResource($patient->refresh()->load(['user', 'doctor', 'subscriptions.doctor']));
    }

    /**
     * Get doctors that the patient is currently subscribed to
     */
    public function myDoctors(Request $request)
    {
        $user = $request->user();
        $patient = Patient::where('user_id', $user->id)->first();

        if (!$patient) {
            return response()->json(['message' => 'Patient profile not found'], 404);
        }

        $doctors = \App\Models\Doctor::whereHas('subscriptions', function ($q) use ($patient) {
            $q->where('patient_id', $patient->id)->where('status', 'active');
        })->with('user')->get();

        return \App\Http\Resources\DoctorResource::collection($doctors);
    }
}
