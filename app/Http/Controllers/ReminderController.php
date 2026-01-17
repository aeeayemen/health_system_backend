<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReminderController extends Controller
{
    public function index(): JsonResponse
    {
        $reminders = Reminder::where('user_id', auth()->id())->get();
        return response()->json(['data' => $reminders]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reminder_time' => 'required|date',
            'type' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $reminder = Reminder::create($validated);

        return response()->json(['data' => $reminder, 'message' => 'Reminder created'], 201);
    }

    public function show($id): JsonResponse
    {
        $reminder = Reminder::where('user_id', auth()->id())->findOrFail($id);
        return response()->json(['data' => $reminder]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $reminder = Reminder::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'reminder_time' => 'sometimes|date',
            'type' => 'nullable|string',
        ]);

        $reminder->update($validated);
        return response()->json(['data' => $reminder, 'message' => 'Reminder updated']);
    }

    public function destroy($id): JsonResponse
    {
        $reminder = Reminder::where('user_id', auth()->id())->findOrFail($id);
        $reminder->delete();
        return response()->json(['message' => 'Reminder deleted']);
    }
}
