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
            'title' => 'nullable|string|max:255',
            'describtion' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'phone_number' => 'required|string',
            'type' => 'required|in:عرض,ترويج', // حسب القيم في الواجهة
            'GPS' => 'nullable|string',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $destinationPath = public_path('uploads/advertisements');
            if (!\Illuminate\Support\Facades\File::exists($destinationPath)) {
                \Illuminate\Support\Facades\File::makeDirectory($destinationPath, 0755, true);
            }
            $image->move($destinationPath, $imageName);
            $validated['image'] = 'uploads/advertisements/' . $imageName;
        }

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
            'title' => 'nullable|string|max:255',
            'describtion' => 'string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'phone_number' => 'string',
            'type' => 'in:عرض,ترويج',
            'GPS' => 'nullable|string',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($ad->image && file_exists(public_path($ad->image))) {
                unlink(public_path($ad->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $destinationPath = public_path('uploads/advertisements');
            if (!\Illuminate\Support\Facades\File::exists($destinationPath)) {
                \Illuminate\Support\Facades\File::makeDirectory($destinationPath, 0755, true);
            }
            $image->move($destinationPath, $imageName);
            $validated['image'] = 'uploads/advertisements/' . $imageName;
        }

        $ad->update($validated);
        return response()->json($ad);
    }

    public function destroy($id)
    {
        $ad = Advertisement::findOrFail($id);

        // Delete image if exists
        if ($ad->image && file_exists(public_path($ad->image))) {
            unlink(public_path($ad->image));
        }

        $ad->delete();
        return response()->json(null, 204);
    }
}