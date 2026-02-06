<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeatureFlag;

class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            ['feature' => 'grade_locking', 'enabled' => true, 'description' => 'Enable grade locking mechanism'],
            ['feature' => 'audit_trail', 'enabled' => true, 'description' => 'Enable audit trail logging'],
            ['feature' => 'schedule_conflict_validation', 'enabled' => true, 'description' => 'Enable schedule conflict detection'],
            ['feature' => 'auto_promotion', 'enabled' => true, 'description' => 'Enable automatic promotion rules'],
            ['feature' => 'alumni_tracking', 'enabled' => true, 'description' => 'Enable alumni tracking module'],
            ['feature' => 'batch_printing', 'enabled' => true, 'description' => 'Enable batch printing functionality'],
        ];

        foreach ($features as $feature) {
            FeatureFlag::create($feature);
        }
    }
}
