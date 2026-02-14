<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Database\Seeder;

class QuizTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'title' => 'Math: Basic Arithmetic',
                'subject' => 'Mathematics',
                'description' => 'Test basic arithmetic operations',
                'questions' => [
                    ['question' => 'What is 7 + 8?', 'options' => ['13', '14', '15', '16'], 'correct_index' => 2, 'time_limit' => 30],
                    ['question' => 'What is 12 × 9?', 'options' => ['98', '108', '118', '128'], 'correct_index' => 1, 'time_limit' => 30],
                    ['question' => 'What is 144 ÷ 12?', 'options' => ['10', '11', '12', '13'], 'correct_index' => 2, 'time_limit' => 30],
                    ['question' => 'What is 25 - 17?', 'options' => ['6', '7', '8', '9'], 'correct_index' => 2, 'time_limit' => 30],
                    ['question' => 'What is 15 × 15?', 'options' => ['215', '225', '235', '245'], 'correct_index' => 1, 'time_limit' => 30],
                ],
            ],
            [
                'title' => 'Science: Solar System',
                'subject' => 'Science',
                'description' => 'Test knowledge about our solar system',
                'questions' => [
                    ['question' => 'Which planet is known as the Red Planet?', 'options' => ['Venus', 'Mars', 'Jupiter', 'Saturn'], 'correct_index' => 1, 'time_limit' => 30],
                    ['question' => 'How many planets are in our solar system?', 'options' => ['7', '8', '9', '10'], 'correct_index' => 1, 'time_limit' => 30],
                    ['question' => 'Which is the largest planet?', 'options' => ['Saturn', 'Uranus', 'Jupiter', 'Neptune'], 'correct_index' => 2, 'time_limit' => 30],
                    ['question' => 'Which planet has rings?', 'options' => ['Mars', 'Jupiter', 'Saturn', 'Venus'], 'correct_index' => 2, 'time_limit' => 30],
                    ['question' => 'What is the closest planet to the Sun?', 'options' => ['Venus', 'Mercury', 'Earth', 'Mars'], 'correct_index' => 1, 'time_limit' => 30],
                ],
            ],
            [
                'title' => 'English: Grammar Basics',
                'subject' => 'English',
                'description' => 'Test your grammar knowledge',
                'questions' => [
                    ['question' => 'Which sentence is correct?', 'options' => ['He don\'t like it.', 'He doesn\'t likes it.', 'He doesn\'t like it.', 'He not like it.'], 'correct_index' => 2, 'time_limit' => 30],
                    ['question' => 'What is the past tense of "go"?', 'options' => ['goed', 'gone', 'went', 'going'], 'correct_index' => 2, 'time_limit' => 30],
                    ['question' => 'Which is a proper noun?', 'options' => ['dog', 'city', 'London', 'book'], 'correct_index' => 2, 'time_limit' => 30],
                    ['question' => 'What type of word is "quickly"?', 'options' => ['Noun', 'Verb', 'Adjective', 'Adverb'], 'correct_index' => 3, 'time_limit' => 30],
                    ['question' => 'Which sentence uses a comma correctly?', 'options' => ['I bought, apples oranges and bananas.', 'I bought apples, oranges, and bananas.', 'I bought apples oranges, and bananas.', 'I, bought apples oranges and bananas.'], 'correct_index' => 1, 'time_limit' => 30],
                ],
            ],
            [
                'title' => 'History: Philippine Heroes',
                'subject' => 'History',
                'description' => 'Test your knowledge of Philippine national heroes',
                'questions' => [
                    ['question' => 'Who is the national hero of the Philippines?', 'options' => ['Andres Bonifacio', 'Jose Rizal', 'Emilio Aguinaldo', 'Apolinario Mabini'], 'correct_index' => 1, 'time_limit' => 30],
                    ['question' => 'Who founded the Katipunan?', 'options' => ['Jose Rizal', 'Emilio Aguinaldo', 'Andres Bonifacio', 'Marcelo del Pilar'], 'correct_index' => 2, 'time_limit' => 30],
                    ['question' => 'What is Jose Rizal\'s full name?', 'options' => ['Jose Protacio Rizal', 'Jose Protacio Mercado Rizal y Alonso Realonda', 'Jose Rizal Mercado', 'Jose Alonso Rizal'], 'correct_index' => 1, 'time_limit' => 30],
                    ['question' => 'Who is known as the "Brains of the Revolution"?', 'options' => ['Andres Bonifacio', 'Apolinario Mabini', 'Emilio Jacinto', 'Antonio Luna'], 'correct_index' => 2, 'time_limit' => 30],
                    ['question' => 'Where was Jose Rizal exiled?', 'options' => ['Cebu', 'Bohol', 'Dapitan', 'Bataan'], 'correct_index' => 2, 'time_limit' => 30],
                ],
            ],
            [
                'title' => 'Quick Check: Understanding',
                'subject' => 'General',
                'description' => 'Quick comprehension check (Yes/No style)',
                'questions' => [
                    ['question' => 'Do you understand the topic?', 'options' => ['Yes, completely', 'Mostly', 'A little', 'Not at all'], 'correct_index' => 0, 'time_limit' => 15],
                    ['question' => 'Was the explanation clear?', 'options' => ['Very clear', 'Somewhat clear', 'A bit confusing', 'Very confusing'], 'correct_index' => 0, 'time_limit' => 15],
                    ['question' => 'Do you have questions?', 'options' => ['No questions', 'One question', 'A few questions', 'Many questions'], 'correct_index' => 0, 'time_limit' => 15],
                ],
            ],
            [
                'title' => 'Ice Breaker: Fun Facts',
                'subject' => 'General',
                'description' => 'Fun ice breaker quiz to start the class',
                'questions' => [
                    ['question' => 'What is the capital of France?', 'options' => ['London', 'Berlin', 'Paris', 'Madrid'], 'correct_index' => 2, 'time_limit' => 20],
                    ['question' => 'How many continents are there?', 'options' => ['5', '6', '7', '8'], 'correct_index' => 2, 'time_limit' => 20],
                    ['question' => 'What is the largest ocean?', 'options' => ['Atlantic', 'Indian', 'Arctic', 'Pacific'], 'correct_index' => 3, 'time_limit' => 20],
                    ['question' => 'What color do you get mixing blue and yellow?', 'options' => ['Green', 'Orange', 'Purple', 'Brown'], 'correct_index' => 0, 'time_limit' => 20],
                ],
            ],
        ];

        foreach ($templates as $template) {
            $quiz = Quiz::create([
                'title' => $template['title'],
                'description' => $template['description'] ?? null,
                'type' => 'template',
                'status' => 'draft',
                'settings' => [
                    'subject' => $template['subject'] ?? 'General',
                    'shuffle_questions' => true,
                    'shuffle_options' => true,
                    'show_results' => 'after_quiz',
                    'allow_review' => true,
                ],
            ]);

            foreach ($template['questions'] as $index => $q) {
                QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question' => $q['question'],
                    'type' => 'multiple_choice',
                    'options' => $q['options'],
                    'correct_index' => $q['correct_index'],
                    'points' => 10,
                    'time_limit' => $q['time_limit'] ?? 30,
                    'order' => $index + 1,
                ]);
            }
        }

        $this->command->info('Quiz templates seeded successfully!');
    }
}
