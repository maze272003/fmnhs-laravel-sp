<?php

namespace App\Services;

use App\Models\PeerTutor;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TutoringSession;
use Illuminate\Support\Collection;

class TutorMatchingService
{
    /**
     * Find a suitable tutor for a student in a given subject.
     */
    public function findTutor(Student $student, Subject $subject): ?PeerTutor
    {
        return PeerTutor::where('subject_id', $subject->id)
            ->where('is_available', true)
            ->where('student_id', '!=', $student->id)
            ->orderByDesc('rating')
            ->first();
    }

    /**
     * Request a tutoring session between a student and a tutor.
     */
    public function requestSession(Student $student, PeerTutor $tutor): TutoringSession
    {
        return TutoringSession::create([
            'tutor_id' => $tutor->id,
            'student_id' => $student->id,
            'subject_id' => $tutor->subject_id,
            'scheduled_at' => now()->addDay(),
            'status' => 'pending',
        ]);
    }

    /**
     * Complete a tutoring session with a rating.
     */
    public function completeSession(TutoringSession $session, ?int $rating = null): TutoringSession
    {
        $session->update([
            'completed_at' => now(),
            'rating' => $rating,
            'status' => 'completed',
        ]);

        // Update tutor's average rating
        if ($rating !== null) {
            $tutor = PeerTutor::find($session->tutor_id);
            if ($tutor) {
                $avgRating = TutoringSession::where('tutor_id', $tutor->id)
                    ->whereNotNull('rating')
                    ->avg('rating');

                $tutor->update(['rating' => round($avgRating, 1)]);
            }
        }

        return $session->fresh();
    }

    /**
     * Get available tutors for a subject.
     */
    public function getAvailableTutors(Subject $subject): Collection
    {
        return PeerTutor::where('subject_id', $subject->id)
            ->where('is_available', true)
            ->with('student')
            ->orderByDesc('rating')
            ->get()
            ->map(fn (PeerTutor $t) => [
                'tutor_id' => $t->id,
                'student_id' => $t->student_id,
                'name' => $t->student
                    ? "{$t->student->first_name} {$t->student->last_name}"
                    : 'Unknown',
                'rating' => $t->rating,
                'sessions_completed' => $t->tutoringSessions()
                    ->where('status', 'completed')
                    ->count(),
            ]);
    }

    /**
     * Get top-rated tutors, optionally filtered by subject.
     */
    public function getTopTutors(?Subject $subject = null): Collection
    {
        $query = PeerTutor::with(['student', 'subject'])
            ->where('is_available', true)
            ->orderByDesc('rating')
            ->limit(10);

        if ($subject) {
            $query->where('subject_id', $subject->id);
        }

        return $query->get()->map(fn (PeerTutor $t) => [
            'tutor_id' => $t->id,
            'name' => $t->student
                ? "{$t->student->first_name} {$t->student->last_name}"
                : 'Unknown',
            'subject' => $t->subject?->name,
            'rating' => $t->rating,
        ]);
    }
}
