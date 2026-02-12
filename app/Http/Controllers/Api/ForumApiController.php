<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumPost;
use App\Models\ForumThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForumApiController extends Controller
{
    /**
     * List forum threads.
     */
    public function threads(Request $request): JsonResponse
    {
        $perPage = min($request->query('per_page', 20), 100);

        $threads = ForumThread::with('user')
            ->withCount('posts')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($threads);
    }

    /**
     * Create a new forum thread.
     */
    public function createThread(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $user = Auth::user();

        $thread = ForumThread::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'category' => $validated['category'] ?? null,
            'user_id' => $user->id,
            'user_type' => get_class($user),
        ]);

        return response()->json($thread, 201);
    }

    /**
     * Show a specific thread with its posts.
     */
    public function showThread(ForumThread $thread): JsonResponse
    {
        $thread->load(['posts' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);

        return response()->json($thread);
    }

    /**
     * Create a post in a thread.
     */
    public function createPost(Request $request, ForumThread $thread): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $user = Auth::user();

        $post = ForumPost::create([
            'thread_id' => $thread->id,
            'body' => $validated['body'],
            'user_id' => $user->id,
            'user_type' => get_class($user),
        ]);

        return response()->json($post, 201);
    }

    /**
     * Mark a post as the solution.
     */
    public function markSolution(ForumPost $post): JsonResponse
    {
        try {
            $post->update(['is_solution' => true]);

            $post->thread()->update(['is_resolved' => true]);

            return response()->json(['message' => 'Post marked as solution.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Search forum threads.
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $query = $validated['q'];

        $threads = ForumThread::where('title', 'like', "%{$query}%")
            ->orWhere('body', 'like', "%{$query}%")
            ->withCount('posts')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($threads);
    }
}
