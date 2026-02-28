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
            'degree' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png,jpg|max:5120',
            'bank_account' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'CV' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'admin_id' => 'nullable|exists:users,id',
        ]);

        // Handle Degree upload
        if ($request->hasFile('degree')) {
            $degree = $request->file('degree');
            $degreeName = time() . '_degree_' . $degree->getClientOriginalName();
            $degree->move(public_path('uploads/doctors/degree'), $degreeName);
            $validated['degree'] = 'uploads/doctors/degree/' . $degreeName;
        }

        // Handle CV upload
        if ($request->hasFile('CV')) {
            $cv = $request->file('CV');
            $cvName = time() . '_cv_' . $cv->getClientOriginalName();
            $cv->move(public_path('uploads/doctors/cv'), $cvName);
            $validated['CV'] = 'uploads/doctors/cv/' . $cvName;
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imageName = time() . '_profile_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/doctors/profile'), $imageName);
            $validated['profile_image'] = 'uploads/doctors/profile/' . $imageName;
        }

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
            'degree' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png,jpg|max:5120',
            'bank_account' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'CV' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'admin_id' => 'nullable|exists:users,id',
        ]);

        // Handle Degree upload
        if ($request->hasFile('degree')) {
            // Delete old degree if exists
            if ($doctor->degree && file_exists(public_path($doctor->degree))) {
                unlink(public_path($doctor->degree));
            }
            $degree = $request->file('degree');
            $degreeName = time() . '_degree_' . $degree->getClientOriginalName();
            $degree->move(public_path('uploads/doctors/degree'), $degreeName);
            $validated['degree'] = 'uploads/doctors/degree/' . $degreeName;
        }

        // Handle CV upload
        if ($request->hasFile('CV')) {
            // Delete old CV if exists
            if ($doctor->CV && file_exists(public_path($doctor->CV))) {
                unlink(public_path($doctor->CV));
            }
            $cv = $request->file('CV');
            $cvName = time() . '_cv_' . $cv->getClientOriginalName();
            $cv->move(public_path('uploads/doctors/cv'), $cvName);
            $validated['CV'] = 'uploads/doctors/cv/' . $cvName;
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($doctor->profile_image && file_exists(public_path($doctor->profile_image))) {
                unlink(public_path($doctor->profile_image));
            }
            $image = $request->file('profile_image');
            $imageName = time() . '_profile_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/doctors/profile'), $imageName);
            $validated['profile_image'] = 'uploads/doctors/profile/' . $imageName;
        }

        $doctor->update($validated);

        return new DoctorResource($doctor);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        // Delete Degree if exists
        if ($doctor->degree && file_exists(public_path($doctor->degree))) {
            unlink(public_path($doctor->degree));
        }
        // Delete CV if exists
        if ($doctor->CV && file_exists(public_path($doctor->CV))) {
            unlink(public_path($doctor->CV));
        }
        // Delete profile image if exists
        if ($doctor->profile_image && file_exists(public_path($doctor->profile_image))) {
            unlink(public_path($doctor->profile_image));
        }

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

        // Notify the doctor about approval
        if ($doctor->user) {
            $doctor->user->notify(new \App\Notifications\DoctorApproved());
        }

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

    /**
     * Get current doctor's profile
     */
    public function myProfile(Request $request)
    {
        $user = $request->user();

        $doctor = Doctor::with('user')
            ->where('user_id', $user->id)
            ->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor profile not found'], 404);
        }

        return new DoctorResource($doctor);
    }

    /**
     * Update current doctor's profile
     */
    public function updateMyProfile(Request $request)
    {
        $user = $request->user();

        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor profile not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'specialization' => 'sometimes|string',
            'bio' => 'nullable|string',
            'consultation_fee' => 'nullable|numeric',
            'is_available' => 'sometimes|boolean',
            'years_of_experience' => 'nullable|integer',
            'phone_number' => 'nullable|string',
            'degree' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png,jpg|max:5120',
            'CV' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Handle Degree upload
        if ($request->hasFile('degree')) {
            if ($doctor->degree && file_exists(public_path($doctor->degree))) {
                unlink(public_path($doctor->degree));
            }
            $degree = $request->file('degree');
            $degreeName = time() . '_degree_' . $degree->getClientOriginalName();
            $degree->move(public_path('uploads/doctors/degree'), $degreeName);
            $validated['degree'] = 'uploads/doctors/degree/' . $degreeName;
        }

        // Handle CV upload
        if ($request->hasFile('CV')) {
            if ($doctor->CV && file_exists(public_path($doctor->CV))) {
                unlink(public_path($doctor->CV));
            }
            $cv = $request->file('CV');
            $cvName = time() . '_cv_' . $cv->getClientOriginalName();
            $cv->move(public_path('uploads/doctors/cv'), $cvName);
            $validated['CV'] = 'uploads/doctors/cv/' . $cvName;
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($doctor->profile_image && file_exists(public_path($doctor->profile_image))) {
                unlink(public_path($doctor->profile_image));
            }
            $image = $request->file('profile_image');
            $imageName = time() . '_profile_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/doctors/profile'), $imageName);
            $validated['profile_image'] = 'uploads/doctors/profile/' . $imageName;
        }

        $doctor->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'doctor' => new DoctorResource($doctor)
        ]);
    }

    /**
     * Get current doctor's ratings
     */
    public function myRates(Request $request)
    {
        $user = $request->user();

        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor profile not found'], 404);
        }

        $rates = \App\Models\Rate::with('user:id,name')
            ->where('doctor_id', $doctor->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $average = $rates->avg('rate');

        return response()->json([
            'average_rating' => round($average ?? 0, 2),
            'total_ratings' => $rates->count(),
            'ratings' => $rates
        ]);
    }

    /**
     * Get doctor's patients list
     */
    public function myPatients(Request $request)
    {
        $user = $request->user();

        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor profile not found'], 404);
        }

        // Get patients through subscriptions or diet plans
        $patients = \App\Models\Patient::whereHas('subscriptions', function ($q) use ($doctor) {
            $q->where('doctor_id', $doctor->id);
        })
            ->orWhereHas('dietPlans', function ($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id);
            })
            ->with('user:id,name,email')
            ->paginate(20);

        return response()->json($patients);
    }

    /**
     * Get a specific patient's progress
     */
    public function patientProgress(Request $request, $patientId)
    {
        $user = $request->user();

        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor profile not found'], 404);
        }

        $patient = \App\Models\Patient::with([
            'user:id,name',
            'measurements',
            'dietPlans' => function ($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id);
            }
        ])->find($patientId);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        // Calculate progress metrics
        $measurements = $patient->measurements->sortBy('measurement_date');
        $firstMeasurement = $measurements->first();
        $lastMeasurement = $measurements->last();

        $weightChange = null;
        if ($firstMeasurement && $lastMeasurement) {
            $weightChange = $lastMeasurement->weight - $firstMeasurement->weight;
        }

        return response()->json([
            'patient' => $patient->user,
            'current_weight' => $patient->current_weight,
            'target_weight' => $patient->target_weight,
            'weight_change' => $weightChange,
            'total_measurements' => $measurements->count(),
            'diet_plans' => $patient->dietPlans,
            'recent_measurements' => $measurements->take(-5)->values()
        ]);
    }

    /**
     * Get patient's calculation history
     */
    public function patientCalculations(Request $request, $patientId)
    {
        $user = $request->user();

        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor profile not found'], 404);
        }

        $patient = \App\Models\Patient::with('user')->find($patientId);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        $calculations = \App\Models\MainCalculation::where('user_id', $patient->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'patient' => $patient->user,
            'calculations' => $calculations
        ]);
    }
}
