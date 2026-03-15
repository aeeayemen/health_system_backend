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

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
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

    /**
     * Join a forum (current user)
     */
    public function join(Request $request, $forumId)
    {
        $forum = Forum::findOrFail($forumId);
        $userId = $request->user()->id;

        // Check if already a member
        $existing = ForumMember::where('forum_id', $forumId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Already a member of this forum'], 400);
        }

        $member = ForumMember::create([
            'forum_id' => $forumId,
            'user_id' => $userId,
        ]);

        return response()->json([
            'message' => 'Joined forum successfully',
            'member' => $member
        ], 201);
    }

    /**
     * Leave a forum (current user)
     */
    public function leave(Request $request, $forumId)
    {
        $forum = Forum::findOrFail($forumId);
        $userId = $request->user()->id;

        $deleted = ForumMember::where('forum_id', $forumId)
            ->where('user_id', $userId)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Not a member of this forum'], 400);
        }

        return response()->json(['message' => 'Left forum successfully']);
    }
}
