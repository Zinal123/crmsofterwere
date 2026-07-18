<?php

namespace Tests\Feature\UiComponents;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class EmptyStateComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_state_renders_icon_and_message(): void
    {
        $html = Blade::render('<x-ui.empty-state icon="ri-inbox-line" message="No invoices yet" />');

        $this->assertStringContainsString('ri-inbox-line', $html);
        $this->assertStringContainsString('No invoices yet', $html);
    }

    public function test_empty_state_renders_optional_action_slot(): void
    {
        $html = Blade::render('<x-ui.empty-state icon="ri-inbox-line" message="No invoices yet"><a href="#">Create one</a></x-ui.empty-state>');

        $this->assertStringContainsString('Create one', $html);
    }
}
