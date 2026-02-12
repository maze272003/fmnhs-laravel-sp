<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AITeachingAssistantService
{
    /**
     * Ask a question in an existing conversation.
     */
    public function ask(AiConversation $conversation, string $question): AiMessage
    {
        // Save the user's message
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $question,
            'tokens_used' => str_word_count($question),
        ]);

        // Get conversation history for context
        $messages = $this->getConversationHistory($conversation);
        $context = $conversation->context;

        // Generate AI response
        $responseText = $this->generateResponse($messages, $context);

        // Save the assistant's response
        $response = AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $responseText,
            'tokens_used' => str_word_count($responseText),
        ]);

        return $response;
    }

    /**
     * Create a new AI conversation for a user.
     */
    public function createConversation($user, ?string $subject = null): AiConversation
    {
        return AiConversation::create([
            'user_type' => get_class($user),
            'user_id' => $user->id,
            'subject' => $subject,
            'context' => $subject ? ['subject' => $subject] : null,
            'status' => 'active',
        ]);
    }

    /**
     * Get all messages in a conversation.
     */
    public function getConversationHistory(AiConversation $conversation): Collection
    {
        return $conversation->messages()
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Generate an AI response based on messages and context.
     */
    public function generateResponse(Collection $messages, ?array $context = null): string
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            return $this->getFallbackResponse($messages->last()?->content ?? '');
        }

        $systemPrompt = 'You are a helpful teaching assistant for a school management system. '
            . 'Help teachers and students with academic questions, lesson planning, and educational content.';

        if ($context && isset($context['subject'])) {
            $systemPrompt .= " The current subject context is: {$context['subject']}.";
        }

        $apiMessages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($messages->takeLast(20) as $msg) {
            $apiMessages[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'messages' => $apiMessages,
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content', 'I could not generate a response.');
            }
        } catch (\Throwable $e) {
            Log::error('AI teaching assistant response failed', ['error' => $e->getMessage()]);
        }

        return $this->getFallbackResponse($messages->last()?->content ?? '');
    }

    /**
     * End a conversation by setting its status to closed.
     */
    public function endConversation(AiConversation $conversation): AiConversation
    {
        $conversation->update(['status' => 'closed']);

        return $conversation->fresh();
    }

    /**
     * Provide a fallback response when AI is unavailable.
     */
    protected function getFallbackResponse(string $question): string
    {
        if (empty($question)) {
            return 'How can I help you today?';
        }

        $lowerQuestion = strtolower($question);

        if (str_contains($lowerQuestion, 'help')) {
            return 'I can help with lesson planning, grading tips, and answering academic questions. What would you like to know?';
        }

        if (str_contains($lowerQuestion, 'lesson') || str_contains($lowerQuestion, 'plan')) {
            return 'For lesson planning, consider defining clear objectives, engaging activities, and assessment methods. Would you like a template?';
        }

        if (str_contains($lowerQuestion, 'grade') || str_contains($lowerQuestion, 'grading')) {
            return 'Effective grading involves clear rubrics, consistent criteria, and constructive feedback. How can I assist with grading?';
        }

        return 'Thank you for your question. The AI service is currently unavailable, but I have recorded your question for follow-up.';
    }
}
