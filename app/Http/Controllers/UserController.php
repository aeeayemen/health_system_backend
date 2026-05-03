<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('roles')->get();
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
            'type' => 'nullable|string',
            'role' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'type' => $validated['type'] ?? 'user',
            
        ]);

        if (isset($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        return response()->json($user->load('roles'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('roles')->findOrFail($id);
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'phone' => 'nullable|string',
            'type' => 'nullable|string',
            'role' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['type'])) {
            $user->type = $validated['type'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        if (isset($validated['phone'])) {
            $user->phone = $validated['phone'];
        }
        if (isset($validated['is_active'])) {
            $user->is_active = $validated['is_active'];
        }

        $user->save();

        // Update role if provided
        if (isset($validated['role'])) {
            // Remove all existing roles and assign the new one
            $user->syncRoles([$validated['role']]);
        }

        return response()->json($user->fresh()->load('roles'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
    public function toggleBan($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();
        return response()->json($user);
    }

    /**
     * Get users with type 'payed'.
     */
    public function payedUsers()
    {
        $users = User::with(['roles', 'patient.doctor.user'])->where('type', 'payed')->get()->map(function($user) {
            $data = $user->toArray();
            if ($user->patient && $user->patient->doctor && $user->patient->doctor->user) {
                $data['doctor_name'] = $user->patient->doctor->user->name;
            } else {
                $data['doctor_name'] = '-';
            }
            return $data;
        });
        return response()->json($users);
    }

    /**
     * Get users with type 'user'.
     */
    public function normalUsers()
    {
        $users = User::with(['roles', 'patient.doctor.user'])->where('type', 'user')->get()->map(function($user) {
            $data = $user->toArray();
            if ($user->patient && $user->patient->doctor && $user->patient->doctor->user) {
                $data['doctor_name'] = $user->patient->doctor->user->name;
            } else {
                $data['doctor_name'] = '-';
            }
            return $data;
        });
        return response()->json($users);
    }
}
