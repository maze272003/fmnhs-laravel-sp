<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameSession;
use App\Models\Student;
use App\Models\VideoConference;
use Illuminate\Support\Facades\DB;

class GameEngineService
{
    public function __construct(
        private readonly GamificationService $gamificationService
    ) {}

    /**
     * Create a new game instance.
     */
    public function createGame(string $type, array $settings, ?VideoConference $conference = null): Game
    {
        return Game::create([
            'conference_id' => $conference?->id,
            'type' => $type,
            'title' => $settings['title'] ?? ucfirst($type) . ' Game',
            'settings' => $settings,
            'status' => 'pending',
            'created_by_id' => $settings['created_by_id'] ?? null,
            'created_by_type' => $settings['created_by_type'] ?? null,
        ]);
    }

    /**
     * Start a game.
     */
    public function startGame(Game $game): Game
    {
        $game->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        return $game->fresh();
    }

    /**
     * End a game and finalize scores.
     */
    public function endGame(Game $game): Game
    {
        $game->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        // Award points to top performers
        $topSessions = $game->sessions()
            ->orderByDesc('score')
            ->limit(3)
            ->get();

        foreach ($topSessions as $index => $session) {
            $this->awardPoints($session);
        }

        return $game->fresh();
    }

    /**
     * Join a student to a game.
     */
    public function joinGame(Game $game, Student $student): GameSession
    {
        return GameSession::updateOrCreate(
            [
                'game_id' => $game->id,
                'student_id' => $student->id,
            ],
            [
                'score' => 0,
                'data' => [],
            ]
        );
    }

    /**
     * Submit an answer for a game question.
     */
    public function submitAnswer(Game $game, Student $student, array $answer): GameSession
    {
        $session = GameSession::where('game_id', $game->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $isCorrect = $this->evaluateAnswer($game, $answer);
        $pointsEarned = $isCorrect ? ($game->settings['points_per_correct'] ?? 10) : 0;

        $data = $session->data ?? [];
        $data['answers'][] = [
            'answer' => $answer,
            'is_correct' => $isCorrect,
            'points' => $pointsEarned,
            'submitted_at' => now()->toIso8601String(),
        ];

        $session->update([
            'score' => $session->score + $pointsEarned,
            'data' => $data,
        ]);

        return $session->fresh();
    }

    /**
     * Get the leaderboard for a game.
     */
    public function getLeaderboard(Game $game): array
    {
        return $game->sessions()
            ->orderByDesc('score')
            ->with('student')
            ->get()
            ->map(fn (GameSession $s, int $i) => [
                'rank' => $i + 1,
                'student_id' => $s->student_id,
                'student_name' => $s->student
                    ? "{$s->student->first_name} {$s->student->last_name}"
                    : 'Unknown',
                'score' => $s->score,
                'completed' => $s->completed_at !== null,
            ])
            ->toArray();
    }

    /**
     * Get the current state of a game.
     */
    public function getGameState(Game $game): array
    {
        return [
            'id' => $game->id,
            'type' => $game->type,
            'title' => $game->title,
            'status' => $game->status,
            'started_at' => $game->started_at,
            'ended_at' => $game->ended_at,
            'settings' => $game->settings,
            'participant_count' => $game->sessions()->count(),
            'leaderboard' => $this->getLeaderboard($game),
        ];
    }

    /**
     * Award gamification points based on a game session.
     */
    public function awardPoints(GameSession $gameSession): void
    {
        $student = $gameSession->student;
        if (!$student) {
            return;
        }

        $this->gamificationService->awardPoints(
            $student,
            $gameSession->score,
            'game',
            $gameSession->game_id,
            "Game score: {$gameSession->score}"
        );

        $gameSession->update(['completed_at' => now()]);
    }

    /**
     * Evaluate if an answer is correct based on game settings.
     */
    protected function evaluateAnswer(Game $game, array $answer): bool
    {
        $correctAnswer = $game->settings['answers'][$answer['question_index'] ?? 0] ?? null;

        if ($correctAnswer === null) {
            return false;
        }

        return ($answer['value'] ?? null) === $correctAnswer;
    }
}
