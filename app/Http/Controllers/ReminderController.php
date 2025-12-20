<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Reminder::with('user');

        // Filter by user for non-admin users
        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        // Optional filter by user_id for admins
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json($query->orderBy('time', 'asc')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'time' => 'required|string|max:100',
            'describtion' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // If user_id not provided, use authenticated user
        if (!isset($validated['user_id'])) {
            $validated['user_id'] = $request->user()->id;
        }

        $reminder = Reminder::create($validated);

        return response()->json($reminder->load('user'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reminder $reminder)
    {
        return response()->json($reminder->load('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reminder $reminder)
    {
        $validated = $request->validate([
            'time' => 'sometimes|string|max:100',
            'describtion' => 'nullable|string|max:255',
        ]);

        $reminder->update($validated);

        return response()->json($reminder->load('user'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reminder $reminder)
    {
        $reminder->delete();

        return response()->json(['message' => 'Reminder deleted successfully']);
    }
}
