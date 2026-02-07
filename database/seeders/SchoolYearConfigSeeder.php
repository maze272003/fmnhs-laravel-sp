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
                'school_year' => '2018-2019',
                'is_active' => false,
                'start_date' => '2018-06-01',
                'end_date' => '2019-05-31',
                'status' => 'closed',
            ],
            [
                'school_year' => '2019-2020',
                'is_active' => false,
                'start_date' => '2019-06-01',
                'end_date' => '2020-05-31',
                'status' => 'closed',
            ],
            [
                'school_year' => '2020-2021',
                'is_active' => false,
                'start_date' => '2020-06-01',
                'end_date' => '2021-05-31',
                'status' => 'closed',
            ],
            [
                'school_year' => '2021-2022',
                'is_active' => false,
                'start_date' => '2021-06-01',
                'end_date' => '2022-05-31',
                'status' => 'closed',
            ],
            [
                'school_year' => '2022-2023',
                'is_active' => false,
                'start_date' => '2022-06-01',
                'end_date' => '2023-05-31',
                'status' => 'closed',
            ],
            [
                'school_year' => '2023-2024',
                'is_active' => false,
                'start_date' => '2023-06-01',
                'end_date' => '2024-05-31',
                'status' => 'closed',
            ],
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
            SchoolYearConfig::firstOrCreate(
                ['school_year' => $sy['school_year']],
                $sy
            );
        }
    }
}
