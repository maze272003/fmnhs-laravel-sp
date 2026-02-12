<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Grade;
use App\Models\Section;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BulkActionService
{
    /**
     * Bulk entry of grades.
     */
    public function bulkGradeEntry(array $grades): array
    {
        $created = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($grades as $gradeData) {
                $result = Grade::updateOrCreate(
                    [
                        'student_id' => $gradeData['student_id'],
                        'subject_id' => $gradeData['subject_id'],
                        'quarter' => $gradeData['quarter'],
                        'school_year_id' => $gradeData['school_year_id'] ?? null,
                    ],
                    [
                        'teacher_id' => $gradeData['teacher_id'],
                        'grade_value' => $gradeData['grade_value'],
                        'school_year' => $gradeData['school_year'] ?? null,
                    ]
                );

                $result->wasRecentlyCreated ? $created++ : $updated++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[] = $e->getMessage();
            Log::error('Bulk grade entry failed', ['error' => $e->getMessage()]);
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    /**
     * Send bulk emails to recipients.
     */
    public function bulkEmail(array $recipients, string $subject, string $body): array
    {
        $sent = 0;
        $failed = 0;

        foreach ($recipients as $email) {
            try {
                Mail::raw($body, function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Bulk email failed', ['email' => $email, 'error' => $e->getMessage()]);
            }
        }

        return ['sent' => $sent, 'failed' => $failed, 'total' => count($recipients)];
    }

    /**
     * Duplicate an assignment across multiple sections.
     */
    public function bulkAssignmentDuplicate(Assignment $assignment, Collection $sections): array
    {
        $duplicated = [];

        foreach ($sections as $section) {
            $copy = $assignment->replicate();
            $copy->section_id = $section->id;
            $copy->save();

            $duplicated[] = [
                'section_id' => $section->id,
                'section_name' => $section->name,
                'assignment_id' => $copy->id,
            ];
        }

        return [
            'original_id' => $assignment->id,
            'duplicated_count' => count($duplicated),
            'duplicated' => $duplicated,
        ];
    }

    /**
     * Bulk import attendance from structured data.
     */
    public function bulkAttendanceImport(array $data): array
    {
        $imported = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($data as $row) {
                DB::table('attendances')->updateOrInsert(
                    [
                        'student_id' => $row['student_id'],
                        'subject_id' => $row['subject_id'] ?? null,
                        'date' => $row['date'],
                    ],
                    [
                        'teacher_id' => $row['teacher_id'] ?? null,
                        'section_id' => $row['section_id'] ?? null,
                        'status' => $row['status'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
                $imported++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[] = $e->getMessage();
            Log::error('Bulk attendance import failed', ['error' => $e->getMessage()]);
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Export model data to CSV format.
     */
    public function exportToCSV(string $model, array $filters = []): string
    {
        $modelClass = "App\\Models\\{$model}";
        if (!class_exists($modelClass)) {
            return '';
        }

        $query = $modelClass::query();
        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }

        $records = $query->get();
        if ($records->isEmpty()) {
            return '';
        }

        $headers = array_keys($records->first()->toArray());
        $csv = implode(',', $headers) . "\n";

        foreach ($records as $record) {
            $row = array_map(function ($value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                return '"' . str_replace('"', '""', (string) $value) . '"';
            }, $record->toArray());

            $csv .= implode(',', $row) . "\n";
        }

        $path = "exports/{$model}-" . now()->format('Ymd-His') . '.csv';
        \Illuminate\Support\Facades\Storage::disk('local')->put($path, $csv);

        return $path;
    }

    /**
     * Import data from a CSV file into a model.
     */
    public function importFromCSV(UploadedFile $file, string $model): array
    {
        $modelClass = "App\\Models\\{$model}";
        if (!class_exists($modelClass)) {
            return ['imported' => 0, 'errors' => ["Model {$model} not found."]];
        }

        $content = $file->getContent();
        $lines = explode("\n", trim($content));
        $headers = str_getcsv(array_shift($lines));

        $imported = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($lines as $lineNum => $line) {
                if (empty(trim($line))) {
                    continue;
                }

                $values = str_getcsv($line);
                if (count($values) !== count($headers)) {
                    $errors[] = "Row " . ($lineNum + 2) . ": column count mismatch.";
                    continue;
                }

                $data = array_combine($headers, $values);
                $modelClass::create($data);
                $imported++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[] = $e->getMessage();
        }

        return ['imported' => $imported, 'errors' => $errors];
    }
}
