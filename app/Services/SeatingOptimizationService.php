<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Seat;
use App\Models\SeatingArrangement;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;

class SeatingOptimizationService
{
    /**
     * Create a new seating arrangement for a section.
     */
    public function createArrangement(Section $section, ?Room $room = null): SeatingArrangement
    {
        return SeatingArrangement::create([
            'section_id' => $section->id,
            'room_id' => $room?->id,
            'name' => "Seating - {$section->name} - " . now()->format('Y-m-d'),
            'layout' => $this->getDefaultLayout($section, $room),
            'is_active' => true,
        ]);
    }

    /**
     * Optimize seating based on a given criteria.
     */
    public function optimizeSeating(SeatingArrangement $arrangement, string $criteria = 'balanced'): SeatingArrangement
    {
        $section = $arrangement->section;
        $students = $section->students()->get();

        // Clear existing seats
        $arrangement->seats()->delete();

        $layout = $arrangement->layout;
        $rows = $layout['rows'] ?? 5;
        $cols = $layout['columns'] ?? 6;

        $orderedStudents = match ($criteria) {
            'alphabetical' => $students->sortBy('last_name')->values(),
            'random' => $students->shuffle(),
            'balanced' => $this->balanceStudents($students),
            default => $students->shuffle(),
        };

        $index = 0;
        for ($r = 1; $r <= $rows && $index < $orderedStudents->count(); $r++) {
            for ($c = 1; $c <= $cols && $index < $orderedStudents->count(); $c++) {
                $this->assignSeat($arrangement, $orderedStudents[$index], $r, $c);
                $index++;
            }
        }

        return $arrangement->fresh()->load('seats');
    }

    /**
     * Assign a student to a specific seat.
     */
    public function assignSeat(SeatingArrangement $arrangement, Student $student, int $row, int $col): Seat
    {
        return Seat::updateOrCreate(
            [
                'seating_arrangement_id' => $arrangement->id,
                'row' => $row,
                'column' => $col,
            ],
            [
                'student_id' => $student->id,
                'label' => "R{$row}C{$col}",
            ]
        );
    }

    /**
     * Generate a visual layout representation of the seating arrangement.
     */
    public function generateLayout(SeatingArrangement $arrangement): array
    {
        $seats = $arrangement->seats()->with('student')->get();
        $layout = $arrangement->layout;
        $rows = $layout['rows'] ?? 5;
        $cols = $layout['columns'] ?? 6;

        $grid = [];
        for ($r = 1; $r <= $rows; $r++) {
            $rowData = [];
            for ($c = 1; $c <= $cols; $c++) {
                $seat = $seats->first(fn (Seat $s) => $s->row === $r && $s->column === $c);
                $rowData[] = [
                    'row' => $r,
                    'column' => $c,
                    'student_id' => $seat?->student_id,
                    'student_name' => $seat?->student
                        ? "{$seat->student->first_name} {$seat->student->last_name}"
                        : null,
                    'label' => $seat?->label ?? "R{$r}C{$c}",
                ];
            }
            $grid[] = $rowData;
        }

        return [
            'arrangement_id' => $arrangement->id,
            'name' => $arrangement->name,
            'rows' => $rows,
            'columns' => $cols,
            'grid' => $grid,
            'total_seats' => $rows * $cols,
            'occupied_seats' => $seats->whereNotNull('student_id')->count(),
        ];
    }

    /**
     * Print/export the seating chart as a text file.
     */
    public function printChart(SeatingArrangement $arrangement): string
    {
        $layout = $this->generateLayout($arrangement);
        $content = "SEATING CHART: {$arrangement->name}\n";
        $content .= str_repeat('=', 60) . "\n\n";
        $content .= "[FRONT OF CLASSROOM]\n\n";

        foreach ($layout['grid'] as $row) {
            $names = array_map(function ($seat) {
                $name = $seat['student_name'] ?? '[ empty ]';

                return str_pad(substr($name, 0, 15), 16);
            }, $row);

            $content .= implode(' | ', $names) . "\n";
        }

        $content .= "\n" . str_repeat('-', 60) . "\n";
        $content .= "Occupied: {$layout['occupied_seats']}/{$layout['total_seats']}\n";

        $path = "seating/chart-{$arrangement->id}-" . now()->timestamp . '.txt';
        Storage::disk('local')->put($path, $content);

        return $path;
    }

    /**
     * Get a default layout based on section size and room capacity.
     */
    protected function getDefaultLayout(Section $section, ?Room $room): array
    {
        $studentCount = $section->students()->count();
        $capacity = $room?->capacity ?? $studentCount;

        $cols = min(8, max(4, (int) ceil(sqrt($capacity))));
        $rows = (int) ceil($capacity / $cols);

        return [
            'rows' => $rows,
            'columns' => $cols,
            'type' => 'grid',
        ];
    }

    /**
     * Balance students for mixed seating (shuffle with grade-aware distribution).
     */
    protected function balanceStudents($students)
    {
        // Simple balanced shuffle: interleave by grade performance
        $sorted = $students->sortBy(fn ($s) => $s->grades()->avg('grade_value') ?? 0);
        $high = $sorted->splice($sorted->count() / 2);
        $low = $sorted;

        $balanced = collect();
        while ($low->isNotEmpty() || $high->isNotEmpty()) {
            if ($high->isNotEmpty()) {
                $balanced->push($high->shift());
            }
            if ($low->isNotEmpty()) {
                $balanced->push($low->shift());
            }
        }

        return $balanced->values();
    }
}
