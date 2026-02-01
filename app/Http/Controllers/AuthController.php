<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
                'user_id' => $user->id,
                'gender' => $request->gender ?? 'male',
            ]);
        } elseif ($role === 'doctor') {
            $doctor = new Doctor();
            $doctor->user_id = $user->id;
            $doctor->name = $user->name;
            $doctor->specialization = $request->specialization ?? 'General';
            $doctor->license_number = $request->license_number ?? 'PENDING-' . time();
            $doctor->save();
        }

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

        if ($user->type === 'doctor') {
            $doctor = $user->doctor;
            if ($doctor && $doctor->application_status !== 'approved') {
                return response()->json([
                    'message' => 'حسابك بانتظار الموافقة من قبل الإدارة'
                ], 403);
            }
        }

        // if (trim(strtolower($user->role)) !== 'admin') {

        //     throw ValidationException::withMessages([
        //         'email' => ['Admins only.'],
        //     ]);
        // }

        $token = $user->createToken('auth_token')->plainTextToken;

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
}
