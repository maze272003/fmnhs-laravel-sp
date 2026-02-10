<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ConferenceNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConferenceNotificationController extends Controller
{
    public function __construct(
        private readonly ConferenceNotificationService $notificationService,
    ) {}

    /**
     * Get unread notifications for the authenticated user.
     */
    public function index(): JsonResponse
    {
        [$type, $id] = $this->resolveRecipient();

        return response()->json([
            'notifications' => $this->notificationService->getUnreadNotifications($type, $id),
        ]);
    }

    /**
     * Mark notifications as read.
     */
    public function markRead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        [$type, $id] = $this->resolveRecipient();

        $count = $this->notificationService->markAsRead($validated['ids'], $type, $id);

        return response()->json(['marked' => $count]);
    }

    private function resolveRecipient(): array
    {
        $teacher = Auth::guard('teacher')->user();
        if ($teacher) {
            return ['teacher', (int) $teacher->id];
        }

        $student = Auth::guard('student')->user();
        if ($student) {
            return ['student', (int) $student->id];
        }

        abort(403);
    }
}
