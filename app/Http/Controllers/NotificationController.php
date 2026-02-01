<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;

class NotificationController extends Controller
{
    /**
     * Get all notifications (admin)
     */
    public function index(Request $request)
    {
        $notifications = DatabaseNotification::orderBy('created_at', 'desc')->paginate(10);

        return response()->json($notifications);
    }

    /**
     * Send notifications to users
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'type' => 'nullable|string',
            'target_users' => 'nullable|array'
        ]);

        $type = $validated['type'] ?? 'general';

        if (!empty($validated['target_users'])) {
            $users = User::whereIn('id', $validated['target_users'])->get();
            Notification::send($users, new GeneralNotification(
                $validated['title'],
                $validated['body'],
                $type
            ));
        }

        return response()->json(['message' => 'Notifications sent']);
    }

    /**
     * Show a specific notification
     */
    public function show($id)
    {
        $notification = DatabaseNotification::findOrFail($id);
        return response()->json($notification);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        DatabaseNotification::destroy($id);
        return response()->json(null, 204);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = DatabaseNotification::findOrFail($id);
        $notification->markAsRead();

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
        $user->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $count = $user->unreadNotifications->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Get user's notifications
     */
    public function myNotifications(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($notifications);
    }
}
