<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolYearConfig;

class SchoolYearConfigSeeder extends Seeder
{
    public function run(): void
    {
        $schoolYears = [
            [
                'school_year' => '2024-2025',
                'is_active' => false,
                'start_date' => '2024-06-01',
                'end_date' => '2025-05-31',
                'status' => 'closed',
            ],
            [
                'school_year' => '2025-2026',
                'is_active' => true,
                'start_date' => '2025-06-01',
                'end_date' => '2026-05-31',
                'status' => 'active',
            ],
        ];

        foreach ($schoolYears as $sy) {
            SchoolYearConfig::create($sy);
        }
    }
}
