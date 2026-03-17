<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'type' => 'nullable|in:user,patient,doctor,admin,payed',
            'gender' => 'nullable|string|in:male,female,ذكر,انثى',
            'degree' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png,jpg|max:5120',
            'cv' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png,jpg|max:5120',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'consultation_fee' => 'nullable|numeric|min:0',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'type' => $validated['type'] ?? 'user',
        ]);

        $role = $validated['type'] ?? 'user';

        // Create Patient or Doctor profile if needed
        if ($role === 'patient') {
            Patient::create([
                'id' => $user->id,
                'user_id' => $user->id,
                'gender' => $request->gender ?? 'male',
            ]);
        } elseif ($role === 'doctor') {
            $doctor = new Doctor();
            $doctor->user_id = $user->id;
            $doctor->name = $user->name;
            $doctor->specialization = $request->specialization ?? 'General';
            $doctor->license_number = $request->license_number ?? 'PENDING-' . time();
            $doctor->gender = $request->gender;
            $doctor->consultation_fee = $request->consultation_fee;
            $doctor->bio = $request->bio;
            $doctor->years_of_experience = $request->years_of_experience;
            $doctor->phone_number = $request->phone_number ?? $validated['phone'] ?? null;
            $doctor->bank_account = $request->bank_account;

            // Handle Degree file upload
            if ($request->hasFile('degree')) {
                $degree = $request->file('degree');
                $degreeName = time() . '_degree_' . $degree->getClientOriginalName();
                $degree->move(public_path('uploads/doctors/degree'), $degreeName);
                $doctor->degree = 'uploads/doctors/degree/' . $degreeName;
            }

            // Handle CV file upload
            if ($request->hasFile('cv')) {
                $cv = $request->file('cv');
                $cvName = time() . '_cv_' . $cv->getClientOriginalName();
                $cv->move(public_path('uploads/doctors/cv'), $cvName);
                $doctor->CV = 'uploads/doctors/cv/' . $cvName;
            }

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $imageName = time() . '_profile_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/doctors/profile'), $imageName);
                $doctor->profile_image = 'uploads/doctors/profile/' . $imageName;
            }

            $doctor->save();

            // Notify all admins about new doctor application
            $admins = User::where('type', 'admin')->get();
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NewDoctorApplication($doctor));
        }

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {


        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        /* 
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Your email address is not verified.'
            ], 403);
        }
        */

        /*
        if ($user->type === 'doctor') {
            $doctor = $user->doctor;
            if ($doctor && $doctor->application_status !== 'approved') {
                return response()->json([
                    'message' => 'حسابك بانتظار الموافقة من قبل الإدارة'
                ], 403);
            }
        }
        */

        // if (trim(strtolower($user->role)) !== 'admin') {

        //     throw ValidationException::withMessages([
        //         'email' => ['Admins only.'],
        //     ]);
        // }

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->load(['doctor', 'patient']);

        return response()->json([
            'message' => 'Logged in successfully',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get current user
     */
    public function me(Request $request)
    {
        return response()->json($request->user()->load(['doctor', 'patient']));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        // Logic to send reset link
        return response()->json(['message' => 'Password reset link sent']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
            'token' => 'required'
        ]);
        // Logic to reset password
        return response()->json(['message' => 'Password reset successfully']);
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent']);
    }

    /**
     * Publicly Resend verification email by email address
     */
    public function resendVerificationEmailPublic(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email already verified'], 400);
            }

            $user->sendEmailVerificationNotification();

            return response()->json(['message' => 'Verification link sent']);
        } catch (\Exception $e) {
            \Log::error('Public Resend Verification Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'حدث خطأ أثناء إرسال البريد: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify email
     */
    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return response()->json(['message' => 'Email verified successfully']);
    }
}
