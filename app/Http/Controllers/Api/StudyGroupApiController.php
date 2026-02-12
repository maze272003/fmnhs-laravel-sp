<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudyGroup;
use App\Models\StudyGroupMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudyGroupApiController extends Controller
{
    /**
     * List study groups.
     */
    public function index(): JsonResponse
    {
        $groups = StudyGroup::withCount('members')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($groups);
    }

    /**
     * Create a new study group.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject' => ['nullable', 'string', 'max:255'],
            'max_members' => ['nullable', 'integer', 'min:2'],
        ]);

        $user = Auth::user();

        $group = StudyGroup::create(array_merge($validated, [
            'created_by_id' => $user->id,
            'created_by_type' => get_class($user),
        ]));

        return response()->json($group, 201);
    }

    /**
     * Show a study group.
     */
    public function show(StudyGroup $group): JsonResponse
    {
        $group->load('members');

        return response()->json($group);
    }

    /**
     * Join a study group.
     */
    public function join(StudyGroup $group): JsonResponse
    {
        $user = Auth::user();

        try {
            $member = StudyGroupMember::firstOrCreate([
                'study_group_id' => $group->id,
                'student_id' => $user->id,
            ], [
                'role' => 'member',
                'joined_at' => now(),
            ]);

            return response()->json($member, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Leave a study group.
     */
    public function leave(StudyGroup $group): JsonResponse
    {
        $user = Auth::user();

        StudyGroupMember::where('study_group_id', $group->id)
            ->where('student_id', $user->id)
            ->delete();

        return response()->json(['message' => 'Left study group successfully.']);
    }

    /**
     * Get members of a study group.
     */
    public function members(StudyGroup $group): JsonResponse
    {
        $members = $group->members()->get();

        return response()->json($members);
    }
}
