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

        // The component's own props (status/variant/icon) must never leak onto
        // the rendered <span> as raw HTML attributes - found live 2026-07-18 on
        // jobs/show.blade.php ($attributes->merge() was including them because
        // the component had no @props() declaration to exclude them).
        $this->assertStringNotContainsString('status="Paid"', $html);
        $this->assertStringNotContainsString('variant="success"', $html);
        $this->assertStringNotContainsString('icon="ri-checkbox-circle-line"', $html);
    }

    public function test_status_badge_requires_icon(): void
    {
        $this->expectException(\Throwable::class);

        Blade::render('<x-ui.status-badge status="Paid" variant="success" />');
    }
}
