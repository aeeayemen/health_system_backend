<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\ForumMember;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $query = Forum::with(['doctor', 'members.user']);
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $forums = $query->paginate(10);
        
        // Transform to include users array directly
        $forums->getCollection()->transform(function ($forum) {
            $forum->users = $forum->members->map(function ($member) {
                return $member->user;
            })->filter();
            unset($forum->members);
            return $forum;
        });
        
        return response()->json($forums);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        $forum = Forum::create($validated);
        return response()->json($forum, 201);
    }

    public function show($id)
    {
        return Forum::with(['doctor', 'members.user'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $forum = Forum::findOrFail($id);
        $forum->update($request->all());
        return response()->json($forum);
    }

    public function destroy($id)
    {
        Forum::destroy($id);
        return response()->json(null, 204);
    }

    public function addUser(Request $request, $forumId)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $member = ForumMember::create([
            'forum_id' => $forumId,
            'user_id' => $validated['user_id'],
        ]);

        return response()->json($member, 201);
    }

    public function removeUser($forumId, $userId)
    {
        ForumMember::where('forum_id', $forumId)
            ->where('user_id', $userId)
            ->delete();

        return response()->json(null, 204);
    }

    public function getMembers($forumId)
    {
        $forum = Forum::findOrFail($forumId);
        $members = ForumMember::with('user')
            ->where('forum_id', $forumId)
            ->get()
            ->map(function ($member) {
                return $member->user;
            })
            ->filter();

        return response()->json($members);
    }
}
