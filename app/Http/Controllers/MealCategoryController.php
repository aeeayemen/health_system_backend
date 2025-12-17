<?php

namespace App\Http\Controllers;

use App\Models\MealCategory;
use Illuminate\Http\Request;

class MealCategoryController extends Controller
{
    public function index()
    {
        return MealCategory::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
            'protein' => 'nullable|numeric|min:0',
            'fat' => 'nullable|numeric|min:0',
            'carbohydrates' => 'nullable|numeric|min:0',
            'energy' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/meal-categories'), $imageName);
            $validated['image'] = 'uploads/meal-categories/' . $imageName;
        }

        $category = MealCategory::create($validated);
        return response()->json($category, 201);
    }

    public function show($id)
    {
        return MealCategory::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $category = MealCategory::findOrFail($id);
        $validated = $request->validate([
            'name_en' => 'sometimes|required|string',
            'name_ar' => 'sometimes|required|string',
            'protein' => 'nullable|numeric|min:0',
            'fat' => 'nullable|numeric|min:0',
            'carbohydrates' => 'nullable|numeric|min:0',
            'energy' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image && file_exists(public_path($category->image))) {
                unlink(public_path($category->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/meal-categories'), $imageName);
            $validated['image'] = 'uploads/meal-categories/' . $imageName;
        }

        $category->update($validated);
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = MealCategory::findOrFail($id);

        // Delete image if exists
        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }

        $category->delete();
        return response()->json(null, 204);
    }
}
