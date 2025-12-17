<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Notification::orderBy('created_at', 'desc')->paginate(10));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'target_users' => 'nullable|array'
        ]);

        if (!empty($validated['target_users'])) {
            foreach ($validated['target_users'] as $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'title' => $validated['title'],
                    'message' => $validated['body'],
                    'is_read' => false
                ]);
            }
        }

        return response()->json(['message' => 'Notifications sent']);
    }

    public function show($id)
    {
        return Notification::findOrFail($id);
    }

    public function destroy($id)
    {
        Notification::destroy($id);
        return response()->json(null, 204);
    }
}
