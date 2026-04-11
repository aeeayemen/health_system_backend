<?php

namespace App\Http\Controllers;

use App\Models\MedicalTest;
use Illuminate\Http\Request;

class MedicalTestController extends Controller
{
    public function index(Request $request)
    {
        $query = MedicalTest::with('user');
        
        // Filter by user_id or patient_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        } elseif ($request->has('patient_id')) {
            $query->where('user_id', $request->patient_id);
        } else {
            // If the current user is a patient, they should only see their own tests by default
            $user = $request->user();
            if ($user && $user->type === 'patient') {
                $query->where('user_id', $user->id);
            }
        }
        
        return \App\Http\Resources\MedicalTestResource::collection($query->latest()->paginate(10));
    }

    public function store(Request $request)
    {
        // Debug: Log the incoming request data
        \Log::info('MedicalTest Store Request:', $request->all());

        // Support both user_id and patient_id
        if (!$request->has('user_id') && $request->has('patient_id')) {
            $request->merge(['user_id' => $request->patient_id]);
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'image' => 'nullable', // Removed strict image rule to allow strings, but will check type manually
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/medical-tests'), $imageName);
            $validated['image'] = 'uploads/medical-tests/' . $imageName;
        }

        $test = MedicalTest::create($validated);
        return new \App\Http\Resources\MedicalTestResource($test->load('user'));
    }

    public function show($id)
    {
        $test = MedicalTest::with('user')->findOrFail($id);
        return new \App\Http\Resources\MedicalTestResource($test);
    }

    public function update(Request $request, $id)
    {
        $test = MedicalTest::findOrFail($id);

        // Support both user_id and patient_id
        if (!$request->has('user_id') && $request->has('patient_id')) {
            $request->merge(['user_id' => $request->patient_id]);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'user_id' => 'sometimes|exists:users,id',
            'image' => 'nullable',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($test->image && file_exists(public_path($test->image))) {
                unlink(public_path($test->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/medical-tests'), $imageName);
            $validated['image'] = 'uploads/medical-tests/' . $imageName;
        }

        $test->update($validated);
        return new \App\Http\Resources\MedicalTestResource($test->load('user'));
    }

    public function destroy($id)
    {
        $test = MedicalTest::findOrFail($id);

        // Delete image if exists
        if ($test->image && file_exists(public_path($test->image))) {
            unlink(public_path($test->image));
        }

        $test->delete();
        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, $id)
    {
        $test = MedicalTest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $test->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Test status updated successfully',
            'data' => $test
        ]);
    }
}
