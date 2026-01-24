<?php

namespace App\Http\Controllers;

use App\Models\DietType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DietTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DietType::query();

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('diet-types', 'public');
        }

        $dietType = DietType::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Diet type created successfully',
            'data' => $dietType
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DietType $dietType)
    {
        return response()->json([
            'success' => true,
            'data' => $dietType
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DietType $dietType)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($dietType->image) {
                Storage::disk('public')->delete($dietType->image);
            }
            $validated['image'] = $request->file('image')->store('diet-types', 'public');
        }

        $dietType->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Diet type updated successfully',
            'data' => $dietType
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DietType $dietType)
    {
        // Delete image if exists
        if ($dietType->image) {
            Storage::disk('public')->delete($dietType->image);
        }

        $dietType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Diet type deleted successfully'
        ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(DietType $dietType)
    {
        $dietType->update([
            'is_active' => !$dietType->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Diet type status updated successfully',
            'data' => $dietType
        ]);
    }
}
