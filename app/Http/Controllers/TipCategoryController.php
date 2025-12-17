<?php

namespace App\Http\Controllers;

use App\Models\TipCategory;
use Illuminate\Http\Request;

class TipCategoryController extends Controller
{
    public function index()
    {
        return TipCategory::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
        ]);
        $category = TipCategory::create($validated);
        return response()->json($category, 201);
    }

    public function show($id)
    {
        return TipCategory::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $category = TipCategory::findOrFail($id);
        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy($id)
    {
        TipCategory::destroy($id);
        return response()->json(null, 204);
    }
}
