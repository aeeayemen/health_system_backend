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

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification
        ]);
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Get user's notifications
     */
    public function myNotifications(Request $request)
    {
        $user = $request->user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($notifications);
    }
}
