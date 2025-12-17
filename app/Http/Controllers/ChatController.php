<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function getConversations(Request $request)
    {
        $user = $request->user();
        // Logic depends on if user is a 'user' or 'doctor' (or admin)
        // Assuming 'type' column in users table or relationship

        if ($user->type === 'doctor') {
            // Get all users this doctor has chatted with
            $userIds = Message::where('doctor_id', $user->doctor->id ?? 0) // Assuming doctor relationship
                ->pluck('user_id')
                ->unique();
            $conversations = User::whereIn('id', $userIds)->get();
        } else {
            // Get all doctors this user has chatted with
            $doctorIds = Message::where('user_id', $user->id)
                ->pluck('doctor_id')
                ->unique();
            $conversations = Doctor::whereIn('id', $doctorIds)->get();
        }

        return response()->json($conversations);
    }

    public function getMessages(Request $request, $id)
    {
        $user = $request->user();

        if ($user->type === 'doctor') {
            $messages = Message::where('doctor_id', $user->doctor->id ?? 0)
                ->where('user_id', $id)
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            $messages = Message::where('user_id', $user->id)
                ->where('doctor_id', $id)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'receiver_id' => 'required|integer', // Could be user_id or doctor_id
            'message' => 'required|string',
        ]);

        if ($user->type === 'doctor') {
            $message = Message::create([
                'doctor_id' => $user->doctor->id ?? 0,
                'user_id' => $validated['receiver_id'],
                'message' => $validated['message'],
                'time' => now()->toTimeString(),
                'date' => now()->toDateString(),
                'read' => 'false'
            ]);
        } else {
            $message = Message::create([
                'user_id' => $user->id,
                'doctor_id' => $validated['receiver_id'],
                'message' => $validated['message'],
                'time' => now()->toTimeString(),
                'date' => now()->toDateString(),
                'read' => 'false'
            ]);
        }

        return response()->json($message, 201);
    }

    public function getAllConversations()
    {
        // Admin only
        $messages = Message::with(['user', 'doctor'])->get();
        // Group by conversation... simplified for now
        return response()->json($messages);
    }

    public function deleteConversation($id)
    {
        $message = Message::find($id);

        if (!$message) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }

        // Delete all messages between this user and doctor
        Message::where('user_id', $message->user_id)
            ->where('doctor_id', $message->doctor_id)
            ->delete();

        return response()->json(['message' => 'Conversation deleted successfully']);
    }

    public function deleteMessage($id)
    {
        Message::destroy($id);
        return response()->json(['message' => 'Message deleted']);
    }
}
