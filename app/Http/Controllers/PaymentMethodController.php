<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PaymentMethod::with('user');

        // Filter by user for non-admin users
        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        // Optional filter by user_id for admins
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json($query->orderBy('is_default', 'desc')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'card_type' => 'required|string|max:50',
            'last_four' => 'required|string|size:4',
            'expiry_date' => 'required|string|max:10',
            'is_default' => 'nullable|boolean',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // If user_id not provided, use authenticated user
        if (!isset($validated['user_id'])) {
            $validated['user_id'] = $request->user()->id;
        }

        // If this is the first payment method or set as default, handle defaults
        if (isset($validated['is_default']) && $validated['is_default']) {
            PaymentMethod::where('user_id', $validated['user_id'])
                ->update(['is_default' => false]);
        }

        $paymentMethod = PaymentMethod::create($validated);

        return response()->json($paymentMethod->load('user'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentMethod $paymentMethod)
    {
        return response()->json($paymentMethod->load('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'card_type' => 'sometimes|string|max:50',
            'last_four' => 'sometimes|string|size:4',
            'expiry_date' => 'sometimes|string|max:10',
            'is_default' => 'nullable|boolean',
        ]);

        // Handle default setting
        if (isset($validated['is_default']) && $validated['is_default']) {
            PaymentMethod::where('user_id', $paymentMethod->user_id)
                ->where('id', '!=', $paymentMethod->id)
                ->update(['is_default' => false]);
        }

        $paymentMethod->update($validated);

        return response()->json($paymentMethod->load('user'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        return response()->json(['message' => 'Payment method deleted successfully']);
    }

    /**
     * Set payment method as default.
     */
    public function setDefault(Request $request, $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        // Remove default from other payment methods for this user
        PaymentMethod::where('user_id', $paymentMethod->user_id)
            ->where('id', '!=', $id)
            ->update(['is_default' => false]);

        // Set this one as default
        $paymentMethod->update(['is_default' => true]);

        return response()->json([
            'message' => 'Payment method set as default',
            'payment_method' => $paymentMethod->load('user')
        ]);
    }
}
