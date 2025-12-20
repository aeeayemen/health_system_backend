<?php

namespace App\Http\Controllers;

use App\Models\ForumPost;
use App\Models\Forum;
use Illuminate\Http\Request;

class ForumPostController extends Controller
{
    /**
     * Get posts for a specific forum
     */
    public function index(Request $request, $forumId)
    {
        $forum = Forum::findOrFail($forumId);

        $query = ForumPost::where('forum_id', $forumId)
            ->with('user:id,name');

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        $posts = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($posts);
    }

    /**
     * Create a new post in a forum
     */
    public function store(Request $request, $forumId)
    {
        $forum = Forum::findOrFail($forumId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post = ForumPost::create([
            'forum_id' => $forumId,
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'likes_count' => 0
        ]);

        return response()->json($post->load('user:id,name'), 201);
    }

    /**
     * Get a specific post
     */
    public function show($id)
    {
        $post = ForumPost::with(['user:id,name', 'forum:id,name'])->findOrFail($id);
        return response()->json($post);
    }

    /**
     * Update a post
     */
    public function update(Request $request, $id)
    {
        $post = ForumPost::findOrFail($id);

        // Check if user owns the post
        if ($post->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        $post->update($validated);

        return response()->json($post);
    }

    /**
     * Delete a post
     */
    public function destroy(Request $request, $id)
    {
        $post = ForumPost::findOrFail($id);

        // Check if user owns the post or is admin
        if ($post->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    /**
     * Like a post
     */
    public function like($id)
    {
        $post = ForumPost::findOrFail($id);
        $post->increment('likes_count');

        return response()->json([
            'message' => 'Post liked',
            'likes_count' => $post->fresh()->likes_count
        ]);
    }

    /**
     * Unlike a post
     */
    public function unlike($id)
    {
        $post = ForumPost::findOrFail($id);
        if ($post->likes_count > 0) {
            $post->decrement('likes_count');
        }

        return response()->json([
            'message' => 'Post unliked',
            'likes_count' => $post->fresh()->likes_count
        ]);
    }
}
