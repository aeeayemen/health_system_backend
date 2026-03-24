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

        try {
            if ($user->type === 'doctor') {
                // Safe null check on doctor relation
                $doctor = $user->doctor;
                if (!$doctor) {
                    return response()->json([]);
                }
                $doctorId = $doctor->id;

                // Get all user IDs who have messages with this doctor
                $messageUserIds = Message::where('doctor_id', $doctorId)
                    ->pluck('user_id')
                    ->unique()
                    ->toArray();

                // Get user IDs from subscriptions (any status)
                $subscriptionUserIds = \App\Models\Subscription::where('doctor_id', $doctorId)
                    ->with('patient')
                    ->get()
                    ->pluck('patient.user_id')
                    ->filter()
                    ->toArray();

                $allUserIds = array_unique(array_merge($messageUserIds, $subscriptionUserIds));

                if (empty($allUserIds)) {
                    return response()->json([]);
                }

                $users = User::whereIn('id', $allUserIds)->get();

                // Build conversation list with last message + unread count
                $conversations = $users->map(function ($u) use ($doctorId) {
                    $lastMessage = Message::where('doctor_id', $doctorId)
                        ->where('user_id', $u->id)
                        ->latest()
                        ->first();

                    $unreadCount = Message::where('doctor_id', $doctorId)
                        ->where('user_id', $u->id)
                        ->where('sender_type', 'user')
                        ->where('read', 'false')
                        ->count();

                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'phone' => $u->phone ?? null,
                        'last_message' => $lastMessage ? $lastMessage->message : null,
                        'last_time' => $lastMessage ? $lastMessage->created_at : null,
                        'unread_count' => $unreadCount,
                    ];
                })->sortByDesc('last_time')->values();

                return response()->json($conversations);

            } else {
                // Patient: get doctors they have messages with or subscriptions to
                $doctorIds = Message::where('user_id', $user->id)
                    ->pluck('doctor_id')
                    ->unique()
                    ->toArray();

                $subscriptionDoctorIds = \App\Models\Subscription::whereHas('patient', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                    ->pluck('doctor_id')
                    ->toArray();

                $allDoctorIds = array_unique(array_merge($doctorIds, $subscriptionDoctorIds));

                if (empty($allDoctorIds)) {
                    return response()->json([]);
                }

                $doctors = Doctor::with('user')->whereIn('id', $allDoctorIds)->get();

                $conversations = $doctors->map(function ($doc) use ($user) {
                    $lastMessage = Message::where('doctor_id', $doc->id)
                        ->where('user_id', $user->id)
                        ->latest()
                        ->first();

                    $unreadCount = Message::where('doctor_id', $doc->id)
                        ->where('user_id', $user->id)
                        ->where('sender_type', 'doctor')
                        ->where('read', 'false')
                        ->count();

                    return [
                        'id' => $doc->id,
                        'user_id' => $doc->user_id,
                        'name' => $doc->user->name ?? $doc->name ?? 'دكتور',
                        'specialization' => $doc->specialization ?? null,
                        'profile_image' => $doc->profile_image ?? null,
                        'last_message' => $lastMessage ? $lastMessage->message : null,
                        'last_time' => $lastMessage ? $lastMessage->created_at : null,
                        'unread_count' => $unreadCount,
                    ];
                })->sortByDesc('last_time')->values();

                return response()->json($conversations);
            }
        } catch (\Exception $e) {
            \Log::error('getConversations error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to load conversations: ' . $e->getMessage()], 500);
        }
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
        try {
            $user = $request->user();
            $validated = $request->validate([
                'receiver_id' => 'required|integer',
                'message' => 'nullable|string',
                'file' => 'nullable|file|max:10240', // 10MB limit
            ]);

            $fileData = [];
            $medicalFileCreated = false;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $uploadPath = public_path('uploads/chat-files');

                if (!\Illuminate\Support\Facades\File::exists($uploadPath)) {
                    \Illuminate\Support\Facades\File::makeDirectory($uploadPath, 0755, true);
                }

                $file->move($uploadPath, $fileName);
                $fileData = [
                    'file_path' => 'uploads/chat-files/' . $fileName,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                ];

                // --- INTEGRATION: Save to Medical Files (Checkups) ---
                $patientId = null;
                if ($user->type === 'doctor') {
                    // Sender is doctor, receiver is user. Find patient record for receiver.
                    $patient = \App\Models\Patient::where('user_id', $validated['receiver_id'])->first();
                    $patientId = $patient ? $patient->id : null;
                } else {
                    // Sender is user. Find patient record for sender.
                    $patient = \App\Models\Patient::where('user_id', $user->id)->first();
                    $patientId = $patient ? $patient->id : null;
                }

                if ($patientId) {
                    \App\Models\MedicalFile::create([
                        'patient_id' => $patientId,
                        'file_name' => $fileData['file_name'],
                        'file_path' => $fileData['file_path'],
                        'file_type' => $fileData['file_type'],
                        'file_size' => \Illuminate\Support\Facades\File::size(public_path($fileData['file_path'])),
                        'description' => 'ملف مرفوع عبر الشات',
                        'status' => 'chat_upload',
                        'uploaded_at' => now(),
                    ]);
                    $medicalFileCreated = true;

                    // --- INTEGRATION: Also Save to Medical Tests (Checkups/الفحوصات) ---
                    // Get the User ID for the patient
                    $patientUser = \App\Models\Patient::find($patientId);
                    if ($patientUser) {
                        \App\Models\MedicalTest::create([
                            'user_id' => $patientUser->user_id,
                            'name' => 'فحص مرفوع من الشات: ' . $fileData['file_name'],
                            'image' => $fileData['file_path'],
                            'status' => 'completed',
                        ]);
                    }
                }
            }

            // Ensure at least message or file is provided
            if (!$request->filled('message') && !$request->hasFile('file')) {
                return response()->json(['message' => 'Message or file is required'], 422);
            }

            $commonData = array_merge([
                'time' => now()->toTimeString(),
                'date' => now()->toDateString(),
                'read' => 'false',
                'message' => $validated['message'] ?? '',
            ], $fileData);

            if ($user->type === 'doctor') {
                $message = Message::create(array_merge($commonData, [
                    'doctor_id' => $user->doctor->id ?? 0,
                    'user_id' => $validated['receiver_id'],
                    'sender_type' => 'doctor'
                ]));
            } else {
                // receiver_id is the user_id of the doctor, we need the actual doctor.id
                $doctor = Doctor::where('user_id', $validated['receiver_id'])->first();
                if (!$doctor) {
                    return response()->json(['message' => 'Doctor not found'], 404);
                }
                $message = Message::create(array_merge($commonData, [
                    'user_id' => $user->id,
                    'doctor_id' => $doctor->id,
                    'sender_type' => 'user'
                ]));
            }

            return response()->json($message, 201);
        } catch (\Exception $e) {
            \Log::error('Chat File Upload Failed: ' . $e->getMessage());
            return response()->json(['message' => 'Chat message failed: ' . $e->getMessage()], 500);
        }
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

    /**
     * Mark a message as read
     */
    public function markAsRead(Request $request, $id)
    {
        $message = Message::findOrFail($id);
        $message->update(['read' => 'true']);

        return response()->json([
            'message' => 'Message marked as read',
            'data' => $message
        ]);
    }

    /**
     * Mark all messages in a conversation as read
     */
    public function markConversationAsRead(Request $request, $conversationId)
    {
        $user = $request->user();

        if ($user->type === 'doctor') {
            Message::where('doctor_id', $user->doctor->id ?? 0)
                ->where('user_id', $conversationId)
                ->update(['read' => 'true']);
        } else {
            Message::where('user_id', $user->id)
                ->where('doctor_id', $conversationId)
                ->update(['read' => 'true']);
        }

        return response()->json(['message' => 'All messages marked as read']);
    }

    /**
     * Get chat history with a specific user/doctor
     */
    public function getHistory(Request $request, $userId)
    {
        $user = $request->user();

        if ($user->type === 'doctor') {
            $messages = Message::where('doctor_id', $user->doctor->id ?? 0)
                ->where('user_id', $userId)
                ->orderBy('created_at', 'asc')
                ->paginate(50);
        } else {
            $messages = Message::where('user_id', $user->id)
                ->where('doctor_id', $userId)
                ->orderBy('created_at', 'asc')
                ->paginate(50);
        }

        return response()->json($messages);
    }

    /**
     * Get unread message count
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();

        if ($user->type === 'doctor') {
            $count = Message::where('doctor_id', $user->doctor->id ?? 0)
                ->where('read', 'false')
                ->count();
        } else {
            $count = Message::where('user_id', $user->id)
                ->where('read', 'false')
                ->count();
        }

        return response()->json(['unread_count' => $count]);
    }
}
