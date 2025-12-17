<?php

namespace App\Http\Controllers;

use App\Models\DietNote;
use Illuminate\Http\Request;

class DietNoteController extends Controller
{
    public function index(Request $request)
    {
        $query = DietNote::with(['user', 'doctor']);
        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:doctors,id',
            'note' => 'nullable|string',
        ]);

        $note = DietNote::create($validated);
        return response()->json($note, 201);
    }

    public function show($id)
    {
        return DietNote::with(['user', 'doctor'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $note = DietNote::findOrFail($id);
        $note->update($request->all());
        return response()->json($note);
    }

    public function destroy($id)
    {
        DietNote::destroy($id);
        return response()->json(null, 204);
    }
}
