<?php

namespace Tests\Feature;

use App\Models\SchoolYearConfig;
use App\Models\FeatureFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolYearConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_year_can_be_activated(): void
    {
        $sy1 = SchoolYearConfig::create([
            'school_year' => '2024-2025',
            'is_active' => true,
            'status' => 'active',
        ]);

        $sy2 = SchoolYearConfig::create([
            'school_year' => '2025-2026',
            'is_active' => false,
            'status' => 'closed',
        ]);

        // Activate SY2
        $sy2->activate();

        $sy1->refresh();
        $sy2->refresh();

        $this->assertFalse($sy1->is_active);
        $this->assertTrue($sy2->is_active);
        $this->assertEquals('active', $sy2->status);
    }

    public function test_active_school_year_can_be_retrieved(): void
    {
        SchoolYearConfig::create([
            'school_year' => '2025-2026',
            'is_active' => true,
            'status' => 'active',
        ]);

        $active = SchoolYearConfig::active();
        $this->assertNotNull($active);
        $this->assertEquals('2025-2026', $active->school_year);
    }

    public function test_school_year_can_be_closed(): void
    {
        $sy = SchoolYearConfig::create([
            'school_year' => '2024-2025',
            'is_active' => true,
            'status' => 'active',
        ]);

        $sy->close();
        $sy->refresh();

        $this->assertFalse($sy->is_active);
        $this->assertEquals('closed', $sy->status);
    }

    public function test_feature_flag_is_enabled(): void
    {
        FeatureFlag::create([
            'feature' => 'grade_locking',
            'enabled' => true,
        ]);

        FeatureFlag::create([
            'feature' => 'batch_printing',
            'enabled' => false,
        ]);

        $this->assertTrue(FeatureFlag::isEnabled('grade_locking'));
        $this->assertFalse(FeatureFlag::isEnabled('batch_printing'));
        // Non-existent feature defaults to true
        $this->assertTrue(FeatureFlag::isEnabled('nonexistent_feature'));
    }
}
