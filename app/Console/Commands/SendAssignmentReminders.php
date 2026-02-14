<?php

namespace App\Console\Commands;

use App\Services\AssignmentReminderService;
use Illuminate\Console\Command;

class SendAssignmentReminders extends Command
{
    protected $signature = 'assignments:send-reminders';
    protected $description = 'Send assignment deadline reminders to students';

    public function handle(AssignmentReminderService $reminderService): int
    {
        $this->info('Sending assignment reminders...');
        
        $upcoming = $reminderService->checkUpcomingDeadlines();
        $totalSent = 0;
        
        foreach ($upcoming as $assignment) {
            $students = $assignment->section?->students ?? collect();
            if ($students->count() > 0) {
                $result = $reminderService->sendReminder($assignment, $students);
                $totalSent += $result['sent'];
            }
        }
        
        $this->info("Sent {$totalSent} reminder notifications for {$upcoming->count()} assignments.");
        
        return self::SUCCESS;
    }
}
