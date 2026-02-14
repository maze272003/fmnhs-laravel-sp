<?php

namespace App\Console\Commands;

use App\Services\ReportGenerationService;
use Illuminate\Console\Command;

class GenerateProgressReports extends Command
{
    protected $signature = 'reports:generate-progress {--period=weekly}';
    protected $description = 'Generate student progress reports';

    public function handle(ReportGenerationService $reportService): int
    {
        $period = $this->option('period');
        $this->info("Generating {$period} progress reports...");
        
        $reports = $reportService->generateBatch($period);
        
        $this->info("Generated {$reports->count()} progress reports.");
        
        return self::SUCCESS;
    }
}
