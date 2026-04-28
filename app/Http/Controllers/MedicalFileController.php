<?php

namespace App\Http\Controllers;

use App\Models\MedicalFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MedicalFileController extends Controller
{
    public function index(Request $request)
    {
        $query = MedicalFile::query();
        
        if ($request->has('patient_id') && !empty($request->patient_id)) {
            $query->where('patient_id', $request->patient_id);
        } elseif ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('patient_id', $request->user_id);
        } else {
            // Default to global files (admin uploaded files for everyone)
            $query->whereNull('patient_id');
        }
        
        $files = $query->with(['patient', 'patient.user'])->latest()->paginate(50);
        return response()->json($files);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'nullable',
            'file_name' => 'nullable|string',
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx|max:10240',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        try {
            // Handle patient_id manually to ensure it's null if empty
            $patientId = $request->input('patient_id');
            $validated['patient_id'] = (!empty($patientId)) ? (int)$patientId : null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');

                // Generate name
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . str_replace(' ', '_', $originalName);

                // Move file
                $uploadPath = public_path('uploads/medical-files');
                if (!File::exists($uploadPath)) {
                    File::makeDirectory($uploadPath, 0755, true);
                }

                $file->move($uploadPath, $fileName);

                $validated['file_path'] = 'uploads/medical-files/' . $fileName;
                $validated['file_name'] = $request->input('file_name', $originalName);
                $validated['file_type'] = $file->getClientOriginalExtension();
                $validated['file_size'] = File::size(public_path('uploads/medical-files/' . $fileName));
            }

            $validated['uploaded_at'] = now();

            // Ensure patient_id is actually null if empty string sent
            if (isset($validated['patient_id']) && empty($validated['patient_id'])) {
                $validated['patient_id'] = null;
            }

            $medicalFile = MedicalFile::create($validated);

            return response()->json($medicalFile, 201);
        } catch (\Exception $e) {
            \Log::error('Medical File Upload Failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Upload failed: ' . $e->getMessage(),
                'error' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function show($id)
    {
        $medicalFile = MedicalFile::with('patient.user')->findOrFail($id);
        return response()->json($medicalFile);
    }

    public function update(Request $request, $id)
    {
        $medicalFile = MedicalFile::findOrFail($id);

        $validated = $request->validate([
            'patient_id' => 'nullable',
            'file_name' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx|max:10240',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        try {
            // Handle patient_id manually to ensure it's null if empty
            if ($request->has('patient_id')) {
                $patientId = $request->input('patient_id');
                $validated['patient_id'] = (!empty($patientId)) ? (int)$patientId : null;
            }

            if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($medicalFile->file_path && file_exists(public_path($medicalFile->file_path))) {
                unlink(public_path($medicalFile->file_path));
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $uploadPath = public_path('uploads/medical-files');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $fileName);

            $validated['file_path'] = 'uploads/medical-files/' . $fileName;

            if (!isset($validated['file_name'])) {
                $validated['file_name'] = $file->getClientOriginalName();
            }
            $validated['file_type'] = $file->getClientOriginalExtension();
            $validated['file_size'] = File::size(public_path('uploads/medical-files/' . $fileName));
            $validated['uploaded_at'] = now();
        }

            $medicalFile->update($validated);

            return response()->json($medicalFile);
        } catch (\Exception $e) {
            \Log::error('Medical File Update Failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Update failed: ' . $e->getMessage(),
                'error' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $medicalFile = MedicalFile::findOrFail($id);

        if ($medicalFile->file_path && file_exists(public_path($medicalFile->file_path))) {
            unlink(public_path($medicalFile->file_path));
        }

        $medicalFile->delete();

        return response()->json(null, 204);
    }

    public function download($id)
    {
        $medicalFile = MedicalFile::findOrFail($id);
        $path = public_path($medicalFile->file_path);

        if (!file_exists($path)) {
            return response()->json(['message' => 'File not found on server'], 404);
        }

        return response()->download($path, $medicalFile->file_name);
    }
}
