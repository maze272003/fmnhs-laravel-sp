<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class ExportService
{
    /**
     * Export data as PDF.
     *
     * @param string $view The Blade view to render
     * @param array $data Data to pass to the view
     * @param string $filename Output filename (without extension)
     * @param string $disk Storage disk
     * @return string The full path to the generated PDF
     */
    public function exportPdf(string $view, array $data, string $filename, string $disk = 'local'): string
    {
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('a4', 'portrait');

        $path = "exports/{$filename}.pdf";
        Storage::disk($disk)->put($path, $pdf->output());

        return Storage::disk($disk)->path($path);
    }

    /**
     * Export data as CSV.
     *
     * @param Collection $data Data collection
     * @param array $columns Column definitions ['header' => 'field']
     * @param string $filename Output filename (without extension)
     * @param string $disk Storage disk
     * @return string The full path to the generated CSV
     */
    public function exportCsv(Collection $data, array $columns, string $filename, string $disk = 'local'): string
    {
        $csv = [];

        // Add headers
        $csv[] = implode(',', array_keys($columns));

        // Add data rows
        foreach ($data as $row) {
            $rowData = [];
            foreach ($columns as $field) {
                $value = data_get($row, $field, '');
                // Escape quotes and wrap in quotes if contains comma
                $value = str_replace('"', '""', (string) $value);
                if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
                    $value = '"' . $value . '"';
                }
                $rowData[] = $value;
            }
            $csv[] = implode(',', $rowData);
        }

        $content = implode("\n", $csv);
        $path = "exports/{$filename}.csv";
        Storage::disk($disk)->put($path, $content);

        return Storage::disk($disk)->path($path);
    }

    /**
     * Export data as Excel-compatible CSV (with BOM for UTF-8).
     *
     * @param Collection $data Data collection
     * @param array $columns Column definitions
     * @param string $filename Output filename
     * @param string $disk Storage disk
     * @return string The full path to the generated file
     */
    public function exportExcelCsv(Collection $data, array $columns, string $filename, string $disk = 'local'): string
    {
        $csv = [];

        // Add BOM for Excel UTF-8 compatibility
        $csv[] = "\xEF\xBB\xBF";

        // Add headers
        $csv[] = implode(',', array_keys($columns));

        // Add data rows
        foreach ($data as $row) {
            $rowData = [];
            foreach ($columns as $field) {
                $value = data_get($row, $field, '');
                $value = str_replace('"', '""', (string) $value);
                if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
                    $value = '"' . $value . '"';
                }
                $rowData[] = $value;
            }
            $csv[] = implode(',', $rowData);
        }

        $content = implode("\n", $csv);
        $path = "exports/{$filename}.csv";
        Storage::disk($disk)->put($path, $content);

        return Storage::disk($disk)->path($path);
    }

    /**
     * Generate a progress report PDF.
     *
     * @param array $reportData Report data from ProgressReport model
     * @param string $studentName Student's full name
     * @return string Path to the generated PDF
     */
    public function generateProgressReportPdf(array $reportData, string $studentName): string
    {
        $filename = sprintf('progress-report-%s-%s', \Str::slug($studentName), now()->format('Ymd'));

        return $this->exportPdf('pdf.progress-report', [
            'data' => $reportData,
            'studentName' => $studentName,
            'generatedAt' => now(),
        ], $filename);
    }

    /**
     * Generate a portfolio PDF.
     *
     * @param mixed $portfolio Portfolio model with items loaded
     * @return string Path to the generated PDF
     */
    public function generatePortfolioPdf($portfolio): string
    {
        $portfolio->load(['items', 'student']);

        $filename = sprintf('portfolio-%s-%s', \Str::slug($portfolio->student->first_name . '-' . $portfolio->student->last_name), now()->format('Ymd'));

        return $this->exportPdf('pdf.portfolio', [
            'portfolio' => $portfolio,
            'generatedAt' => now(),
        ], $filename);
    }

    /**
     * Generate a class list PDF.
     *
     * @param mixed $section Section model with students loaded
     * @return string Path to the generated PDF
     */
    public function generateClassListPdf($section): string
    {
        $section->load(['students']);

        $filename = sprintf('class-list-%s-%s', \Str::slug($section->name), now()->format('Ymd'));

        return $this->exportPdf('pdf.class-list', [
            'section' => $section,
            'generatedAt' => now(),
        ], $filename);
    }

    /**
     * Generate a grades report PDF.
     *
     * @param Collection $grades Grades collection
     * @param string $studentName Student's full name
     * @param string $period Grading period
     * @return string Path to the generated PDF
     */
    public function generateGradesReportPdf(Collection $grades, string $studentName, string $period): string
    {
        $filename = sprintf('grades-%s-%s-%s', \Str::slug($studentName), \Str::slug($period), now()->format('Ymd'));

        return $this->exportPdf('pdf.grades-report', [
            'grades' => $grades,
            'studentName' => $studentName,
            'period' => $period,
            'generatedAt' => now(),
        ], $filename);
    }
}
