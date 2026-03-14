<?php

namespace App\Http\Controllers;

use App\Models\Tip;
use App\Models\Doctor;
use App\Models\Advertisement;
use App\Models\Forum;
use App\Models\Diet;
use Illuminate\Http\Request;

/**
 * Public Controller - Guest accessible endpoints (no authentication required)
 */
class PublicController extends Controller
{
    /**
     * Get public tips
     */
    public function tips(Request $request)
    {
        $query = Tip::with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->paginate(10));
    }

    /**
     * Get approved doctors list
     */
    public function doctors(Request $request)
    {
        $query = Doctor::with('user:id,email')
            ->where('application_status', 'approved')
            ->where('is_available', true);

        if ($request->has('specialization')) {
            $query->where('specialization', $request->specialization);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $doctors = $query->get([
            'id',
            'user_id',
            'name',
            'specialization',
            'bio',
            'consultation_fee',
            'profile_image',
            'years_of_experience',
            'phone_number',
            'bank_account',
            'rating',
            'gender'
        ]);

        $doctors->transform(function ($doctor) {
            $data = $doctor->toArray();
            $data['email'] = $doctor->user ? $doctor->user->email : null;
            unset($data['user']);
            return $data;
        });

        return response()->json($doctors);
    }

    /**
     * Get active advertisements
     */
    public function advertisements()
    {
        $ads = Advertisement::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($ads);
    }

    /**
     * Get public forums list
     */
    public function forums(Request $request)
    {
        $query = Forum::with('doctor:id,name');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $forums = $query->withCount('members')->paginate(10);

        return response()->json($forums);
    }

    /**
     * Get forum posts (public view)
     */
    public function forumPosts($forumId)
    {
        $forum = Forum::findOrFail($forumId);

        $posts = $forum->posts()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($posts);
    }

    /**
     * Get diets preview (limited info for guests)
     */
    public function dietsPreview()
    {
        $diets = Diet::with('doctor:id,name')
            ->select('id', 'price', 'doctor_id', 'periods', 'created_at')
            ->paginate(10);

        return response()->json($diets);
    }
}
