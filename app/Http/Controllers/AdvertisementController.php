<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvertisementController extends Controller
{
    public function index(Request $request)
    {
        $query = Advertisement::query();
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('describtion', 'like', '%' . $search . '%');
            });
        }
        // يمكن إضافة تصفية بالنوع إذا كان موجودًا في الواجهة
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        return response()->json($query->orderBy('id', 'desc')->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([

            'describtion' => 'required|string',
            'image' => 'required|string',
            'phone_number' => 'required|string',
            'type' => 'required|in:عرض,ترويج', // حسب القيم في الواجهة
            'GPS' => 'nullable|string',
        ]);

        // إضافة admin_id من المستخدم الحالي إذا كان مسجلاً
        // إضافة admin_id من المستخدم الحالي إذا كان مسجلاً، وإلا القيمة الافتراضية 1
        $validated['admin_id'] = Auth::id() ?? 1;
        $validated['date'] = now();

        $ad = Advertisement::create($validated);
        return response()->json($ad, 201);
    }

    public function show($id)
    {
        return Advertisement::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $ad = Advertisement::findOrFail($id);
        $validated = $request->validate([
            'describtion' => 'string',
            'image' => 'string',
            'phone_number' => 'string',
            'type' => 'in:عرض,ترويج',
            'GPS' => 'nullable|string',
        ]);

        $ad->update($validated);
        return response()->json($ad);
    }

    public function destroy($id)
    {
        Advertisement::destroy($id);
        return response()->json(null, 204);
    }
}