<?php

namespace App\Http\Controllers;

use App\Models\Athkar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AthkarController extends Controller
{
    public function index(Request $request)
    {
        $query = Athkar::with('admin');

        // التصفية بحسب النوع (الصباح، المساء، الخ)
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // البحث
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        return response()->json($query->orderBy('id', 'asc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string', // مثلا: 'morning', 'evening'
            'title' => 'nullable|string',    // النص العريض
            'content' => 'required|string',  // محتوى الذكر
            'repetition' => 'nullable|integer|min:1', // مرات التكرار
        ]);

        $validated['admin_id'] = Auth::id() ?? 1;
        $validated['repetition'] = $validated['repetition'] ?? 1;

        $athkar = Athkar::create($validated);
        return response()->json($athkar, 201);
    }

    public function show($id)
    {
        return Athkar::with('admin')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $athkar = Athkar::findOrFail($id);

        $validated = $request->validate([
            'category' => 'sometimes|string',
            'title' => 'nullable|string',
            'content' => 'sometimes|string',
            'repetition' => 'nullable|integer|min:1',
        ]);

        $athkar->update($validated);
        return response()->json($athkar);
    }

    public function destroy($id)
    {
        $athkar = Athkar::findOrFail($id);
        $athkar->delete();
        return response()->json(null, 204);
    }
}
