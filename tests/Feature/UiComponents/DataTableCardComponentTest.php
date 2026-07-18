<?php

namespace Tests\Feature\UiComponents;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DataTableCardComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_title_and_slot_content(): void
    {
        $html = Blade::render('<x-ui.data-table-card title="Invoices"><table id="test-table"></table></x-ui.data-table-card>');

        $this->assertStringContainsString('Invoices', $html);
        $this->assertStringContainsString('id="test-table"', $html);
    }

    public function test_renders_optional_create_button(): void
    {
        $html = Blade::render('<x-ui.data-table-card title="Invoices" create-route="/apps-invoices-create" create-label="Create Invoice"><table></table></x-ui.data-table-card>');

        $this->assertStringContainsString('Create Invoice', $html);
        $this->assertStringContainsString('/apps-invoices-create', $html);
    }

    public function test_omits_create_button_when_not_provided(): void
    {
        $html = Blade::render('<x-ui.data-table-card title="Users"><table></table></x-ui.data-table-card>');

        $this->assertStringNotContainsString('btn-success', $html);
    }
}
