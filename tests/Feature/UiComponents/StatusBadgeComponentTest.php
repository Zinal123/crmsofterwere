<?php

namespace Tests\Feature\UiComponents;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class StatusBadgeComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_badge_renders_color_icon_and_text(): void
    {
        $html = Blade::render('<x-ui.status-badge status="Paid" variant="success" icon="ri-checkbox-circle-line" />');

        $this->assertStringContainsString('bg-success-subtle', $html);
        $this->assertStringContainsString('text-success', $html);
        $this->assertStringContainsString('ri-checkbox-circle-line', $html);
        $this->assertStringContainsString('Paid', $html);
    }

    public function test_status_badge_requires_icon(): void
    {
        $this->expectException(\Throwable::class);

        Blade::render('<x-ui.status-badge status="Paid" variant="success" />');
    }
}
