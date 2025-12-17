<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubscribedUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Patient::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Allow user_id to be passed as id, or id itself
        $data = $request->all();
        if (isset($data['user_id']) && !isset($data['id'])) {
            $data['id'] = $data['user_id'];
        }
        $request->merge($data);

        $validated = $request->validate([
            'id' => 'required|exists:users,id|unique:subscribed_users,id',
            'fullname' => 'nullable|string|max:100',
            'gender' => 'nullable|string|max:100',
            'height' => 'nullable|integer',
            'weight' => 'nullable|integer',
            'phone_number' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048', // Validate image file
            'birthdate' => 'nullable|string|max:100',
            'physical_activity' => 'nullable|string|max:100',
            'medical' => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('subscribed_users', 'public');
            $validated['image'] = $path;
        }

        $subscribedUser = Patient::create($validated);

        return response()->json($subscribedUser, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $subscribedUser = Patient::find($id);

        if (!$subscribedUser) {
            return response()->json(['message' => 'Subscribed user not found'], 404);
        }

        return response()->json($subscribedUser);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $subscribedUser = Patient::find($id);

        if (!$subscribedUser) {
            return response()->json(['message' => 'Subscribed user not found'], 404);
        }

        $validated = $request->validate([
            'fullname' => 'nullable|string|max:100',
            'gender' => 'nullable|string|max:100',
            'height' => 'nullable|integer',
            'weight' => 'nullable|integer',
            'phone_number' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
            'birthdate' => 'nullable|string|max:100',
            'physical_activity' => 'nullable|string|max:100',
            'medical' => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($subscribedUser->image) {
                Storage::disk('public')->delete($subscribedUser->image);
            }
            $path = $request->file('image')->store('subscribed_users', 'public');
            $validated['image'] = $path;
        }

        $subscribedUser->update($validated);

        return response()->json($subscribedUser);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subscribedUser = Patient::find($id);

        if (!$subscribedUser) {
            return response()->json(['message' => 'Subscribed user not found'], 404);
        }

        if ($subscribedUser->image) {
            Storage::disk('public')->delete($subscribedUser->image);
        }

        $subscribedUser->delete();

        return response()->json(['message' => 'Subscribed user deleted successfully']);
    }
}
