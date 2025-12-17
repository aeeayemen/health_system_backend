<?php

namespace App\Http\Controllers;

use App\Models\Tip;
use Illuminate\Http\Request;

class TipController extends Controller
{
    public function index(Request $request)
    {
        $query = Tip::with(['admin', 'category']);

        if ($request->has('search')) {
            $query->where('describtion', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'describtion' => 'nullable|string',
            'admin_id' => 'nullable|exists:users,id',
            'category_id' => 'nullable|exists:tip_categories,id',
            'date' => 'nullable|string',
        ]);

        $tip = Tip::create($validated);
        return response()->json($tip, 201);
    }

    public function show($id)
    {
        return Tip::with(['admin', 'category'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $tip = Tip::findOrFail($id);
        $tip->update($request->all());
        return response()->json($tip);
    }

    public function destroy($id)
    {
        Tip::destroy($id);
        return response()->json(null, 204);
    }
}
