<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubscribedUserController extends Controller
{
    /**
     * كل الاشتراكات (المبدئي + المدفوع + المقبول)
     */
    public function index(Request $request)
    {
        $query = Subscription::with(['patient.user', 'doctor.user'])
            ->latest();

        // فلترة اختيارية
        if ($request->user_id) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
        }

        $subscriptions = $query->get()->map(function ($sub) {
            return [
                'id' => $sub->id,

                'patient_id' => $sub->patient_id,
                'patient_name' => optional($sub->patient->user)->name ?? $sub->patient->fullname,

                'doctor_id' => $sub->doctor_id,
                'doctor_name' => optional($sub->doctor->user)->name ?? $sub->doctor->name,

                'type' => $sub->plan_type,
                'price' => $sub->price,

                'start_date' => $sub->start_date,
                'end_date' => $sub->end_date,

                'status' => $sub->status,

                'receipt_image' => $sub->receipt_image
                    ? \Storage::url($sub->receipt_image)
                    : null,
            ];
        });

        return response()->json($subscriptions);
    }

    /**
     * إنشاء اشتراك جديد (مبدئي دائمًا pending)
     */
    public function store(Request $request)
    {
        $rules = [
            'doctor_id' => 'required|exists:doctors,id',
            'type' => 'nullable|string',
            'price' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'receipt_image' => 'nullable|image|max:2048',
        ];

        // قبول إما user_id أو patient_id
        if ($request->has('patient_id')) {
            $rules['patient_id'] = 'required|exists:patients,id';
        } elseif ($request->has('user_id')) {
            $rules['user_id'] = 'required|exists:users,id';
        } else {
            return response()->json(['message' => 'Either patient_id or user_id is required'], 422);
        }

        $data = $request->validate($rules);

        // استنتاج patient_id الفعلي
        if (isset($data['patient_id'])) {
            $patientId = $data['patient_id'];
        } else {
            // البحث عن patient المرتبط بـ user_id
            $patient = \App\Models\Patient::where('user_id', $data['user_id'])->first();
            if (!$patient) {
                // إذا لم يوجد patient، قم بإنشائه تلقائياً
                $user = \App\Models\User::find($data['user_id']);
                $patient = \App\Models\Patient::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    // يمكن إضافة قيم افتراضية للحقول الأخرى
                ]);
            }
            $patientId = $patient->id;
        }

        // تخزين الصورة إن وجدت
        $receiptPath = null;
        if ($request->hasFile('receipt_image')) {
            $receiptPath = $request->file('receipt_image')->store('receipts', 'public');
        }

        $subscription = Subscription::create([
            'patient_id' => $patientId,
            'doctor_id' => $data['doctor_id'],
            'plan_type' => $data['type'] ?? 'basic',
            'price' => $data['price'] ?? 0,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'status' => 'pending',
            'receipt_image' => $receiptPath,
        ]);

        return response()->json($subscription, 201);
    }
    /**
     * عرض اشتراك واحد
     */
    public function show($id)
    {
        $sub = Subscription::with(['patient.user', 'doctor.user'])->find($id);

        if (!$sub) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json([
            'id' => $sub->id,
            'patient_name' => optional($sub->patient->user)->name,
            'doctor_name' => optional($sub->doctor->user)->name,
            'type' => $sub->plan_type,
            'price' => $sub->price,
            'status' => $sub->status,
        ]);
    }

    /**
     * تحديث الاشتراك (الأدمن فقط)
     */
    public function update(Request $request, $id)
    {
        $sub = Subscription::find($id);

        if (!$sub) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $data = $request->validate([
            'status' => 'required|in:pending,active,rejected,expired',
        ]);

        // هنا القرار الحقيقي
        $sub->status = $data['status'];
        $sub->save();

        // لو صار active → نحدث المستخدم
        if ($sub->status === 'active') {
            $patient = $sub->patient;

            if ($patient && $patient->user) {
                $patient->user->update([
                    'type' => 'payed'
                ]);
            }

            // ربط الطبيب بالمريض
            $patient->update([
                'current_doctor_id' => $sub->doctor_id
            ]);
        }

        return response()->json($sub);
    }

    /**
     * حذف اشتراك
     */
    public function destroy($id)
    {
        $sub = Subscription::find($id);

        if (!$sub) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if ($sub->receipt_image) {
            Storage::disk('public')->delete($sub->receipt_image);
        }

        $sub->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function byUser($userId)
    {
        return \App\Models\Subscription::with(['patient.user', 'doctor.user'])
            ->whereHas('patient', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('status', 'active')
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'patient_name' => $sub->patient->user->name ?? null,
                    'doctor_name' => $sub->doctor->user->name ?? null,
                    'price' => $sub->price,
                    'status' => $sub->status,
                ];
            });
    }
}