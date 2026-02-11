<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Badge;
use Illuminate\Database\Seeder;

class GamificationSeeder extends Seeder
{
    public function run(): void
    {
        // Create Badges
        $badges = [
            // Academic Badges
            [
                'name' => 'Quiz Master',
                'slug' => 'quiz-master',
                'description' => 'Complete 10 quizzes with perfect scores',
                'icon' => '🏆',
                'color' => 'gold',
                'category' => 'academic',
                'unlock_criteria' => ['type' => 'quiz_perfect_scores', 'threshold' => 10],
                'points_value' => 50,
            ],
            [
                'name' => 'Scholar',
                'slug' => 'scholar',
                'description' => 'Earn 500 total points',
                'icon' => '📚',
                'color' => 'blue',
                'category' => 'academic',
                'unlock_criteria' => ['type' => 'points', 'threshold' => 500],
                'points_value' => 25,
            ],
            [
                'name' => 'Genius',
                'slug' => 'genius',
                'description' => 'Earn 1000 total points',
                'icon' => '🧠',
                'color' => 'purple',
                'category' => 'academic',
                'unlock_criteria' => ['type' => 'points', 'threshold' => 1000],
                'points_value' => 100,
            ],
            
            // Attendance Badges
            [
                'name' => 'Perfect Attendance',
                'slug' => 'perfect-attendance',
                'description' => 'Maintain perfect attendance for a month',
                'icon' => '✅',
                'color' => 'green',
                'category' => 'attendance',
                'unlock_criteria' => ['type' => 'attendance_streak', 'threshold' => 30],
                'points_value' => 50,
            ],
            [
                'name' => 'Early Bird',
                'slug' => 'early-bird',
                'description' => 'Join 10 conferences within the first 5 minutes',
                'icon' => '🐦',
                'color' => 'yellow',
                'category' => 'attendance',
                'unlock_criteria' => ['type' => 'early_joins', 'threshold' => 10],
                'points_value' => 20,
            ],
            
            // Participation Badges
            [
                'name' => 'Active Learner',
                'slug' => 'active-learner',
                'description' => 'Participate in 50 conferences',
                'icon' => '💬',
                'color' => 'blue',
                'category' => 'participation',
                'unlock_criteria' => ['type' => 'conference_count', 'threshold' => 50],
                'points_value' => 30,
            ],
            [
                'name' => 'Helper',
                'slug' => 'helper',
                'description' => 'Actively help classmates',
                'icon' => '🤝',
                'color' => 'orange',
                'category' => 'participation',
                'unlock_criteria' => ['type' => 'help_count', 'threshold' => 20],
                'points_value' => 25,
            ],
            
            // Special Badges
            [
                'name' => 'Badge Collector',
                'slug' => 'badge-collector',
                'description' => 'Earn 5 different badges',
                'icon' => '🎖️',
                'color' => 'rainbow',
                'category' => 'special',
                'unlock_criteria' => ['type' => 'badges_count', 'threshold' => 5],
                'points_value' => 40,
            ],
            [
                'name' => 'Leaderboard Champion',
                'slug' => 'leaderboard-champion',
                'description' => 'Reach top 3 on the leaderboard',
                'icon' => '👑',
                'color' => 'gold',
                'category' => 'special',
                'unlock_criteria' => ['type' => 'leaderboard_rank', 'threshold' => 3],
                'points_value' => 100,
            ],
        ];

        foreach ($badges as $badgeData) {
            Badge::create($badgeData);
        }

        // Create Achievements
        $achievements = [
            // Milestone Achievements
            [
                'name' => 'First Quiz',
                'slug' => 'first-quiz',
                'description' => 'Complete your first quiz',
                'type' => 'milestone',
                'requirements' => ['type' => 'quiz_count', 'threshold' => 1],
                'points_reward' => 10,
                'badge_id' => null,
            ],
            [
                'name' => '100 Points',
                'slug' => '100-points',
                'description' => 'Earn your first 100 points',
                'type' => 'milestone',
                'requirements' => ['type' => 'points', 'threshold' => 100],
                'points_reward' => 10,
                'badge_id' => null,
            ],
            [
                'name' => 'Perfect Score',
                'slug' => 'perfect-score',
                'description' => 'Get 100% on any quiz',
                'type' => 'milestone',
                'requirements' => ['type' => 'quiz_perfect_scores', 'threshold' => 1],
                'points_reward' => 15,
                'badge_id' => null,
            ],
            
            // Streak Achievements
            [
                'name' => '7-Day Streak',
                'slug' => '7-day-streak',
                'description' => 'Attend classes for 7 consecutive days',
                'type' => 'streak',
                'requirements' => ['type' => 'attendance_streak', 'threshold' => 7],
                'points_reward' => 20,
                'badge_id' => null,
            ],
            [
                'name' => '30-Day Streak',
                'slug' => '30-day-streak',
                'description' => 'Attend classes for 30 consecutive days',
                'type' => 'streak',
                'requirements' => ['type' => 'attendance_streak', 'threshold' => 30],
                'points_reward' => 100,
                'badge_id' => Badge::where('slug', 'perfect-attendance')->first()?->id,
            ],
            
            // Challenge Achievements
            [
                'name' => 'Quiz Champion',
                'slug' => 'quiz-champion',
                'description' => 'Score 90% or higher on 5 quizzes',
                'type' => 'challenge',
                'requirements' => ['type' => 'quiz_high_scores', 'threshold' => 5],
                'points_reward' => 30,
                'badge_id' => null,
            ],
            [
                'name' => 'Speed Learner',
                'slug' => 'speed-learner',
                'description' => 'Complete 5 quizzes in under 2 minutes each',
                'type' => 'challenge',
                'requirements' => ['type' => 'quiz_speed', 'threshold' => 5],
                'points_reward' => 25,
                'badge_id' => null,
            ],
        ];

        foreach ($achievements as $achievementData) {
            Achievement::create($achievementData);
        }

        $this->command->info('Gamification seeder completed successfully!');
        $this->command->info('Created ' . count($badges) . ' badges and ' . count($achievements) . ' achievements.');
    }
}
