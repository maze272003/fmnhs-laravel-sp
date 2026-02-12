<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Services\AITeachingAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AIAssistantApiController extends Controller
{
    public function __construct(
        private readonly AITeachingAssistantService $aiService,
    ) {}

    /**
     * Start a new AI conversation.
     */
    public function startConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        try {
            $conversation = $this->aiService->createConversation(
                $user,
                $validated['subject'] ?? null
            );

            return response()->json($conversation, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Ask a question in a conversation.
     */
    public function ask(Request $request, AiConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $response = $this->aiService->ask($conversation, $validated['message']);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get conversation history.
     */
    public function history(AiConversation $conversation): JsonResponse
    {
        $conversation->load('messages');

        return response()->json($conversation);
    }

    /**
     * End a conversation.
     */
    public function endConversation(AiConversation $conversation): JsonResponse
    {
        try {
            $this->aiService->endConversation($conversation);

            return response()->json(['message' => 'Conversation ended.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
