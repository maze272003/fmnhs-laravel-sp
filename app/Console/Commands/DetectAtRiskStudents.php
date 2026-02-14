<?php

namespace App\Console\Commands;

use App\Services\AtRiskDetectionService;
use Illuminate\Console\Command;

class DetectAtRiskStudents extends Command
{
    protected $signature = 'students:detect-at-risk';
    protected $description = 'Detect students at risk based on attendance and grades';

    public function handle(AtRiskDetectionService $detectionService): int
    {
        $this->info('Detecting at-risk students...');
        
        $atRisk = $detectionService->detectAtRiskStudents();
        
        $this->info("Found {$atRisk->count()} at-risk students and created alerts.");
        
        return self::SUCCESS;
    }
}
