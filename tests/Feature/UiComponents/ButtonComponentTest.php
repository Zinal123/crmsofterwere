<?php

namespace Tests\Feature\UiComponents;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ButtonComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_button_component_renders_variant_and_size_classes(): void
    {
        $html = Blade::render('<x-ui.button variant="success" size="sm">Create Invoice</x-ui.button>');

        $this->assertStringContainsString('btn-success', $html);
        $this->assertStringContainsString('btn-sm', $html);
        $this->assertStringContainsString('Create Invoice', $html);
    }

    public function test_button_component_renders_icon(): void
    {
        $html = \Illuminate\Support\Facades\Blade::render('<x-ui.button variant="success" icon="ri-add-line">Create Invoice</x-ui.button>');

        $this->assertStringContainsString('ri-add-line', $html);
    }

    public function test_icon_only_button_requires_aria_label(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Laravel's CompilerEngine wraps any exception thrown while rendering a
        // Blade template in Illuminate\View\ViewException, and does so again at
        // each nested view boundary the exception passes through (the anonymous
        // component's own view, then the outer Blade::render() view). Unwrap
        // fully here so this assertion targets the actual guard exception the
        // component throws, per the brief's intent.
        try {
            \Illuminate\Support\Facades\Blade::render('<x-ui.button variant="primary" icon="ri-delete-bin-2-line"></x-ui.button>');
        } catch (\Illuminate\View\ViewException $e) {
            $previous = $e;
            while ($previous instanceof \Illuminate\View\ViewException && $previous->getPrevious() !== null) {
                $previous = $previous->getPrevious();
            }
            throw $previous;
        }
    }

    public function test_icon_only_button_with_aria_label_renders_correctly(): void
    {
        $html = \Illuminate\Support\Facades\Blade::render('<x-ui.button variant="primary" icon="ri-delete-bin-2-line" ariaLabel="Delete selected"></x-ui.button>');

        $this->assertStringContainsString('aria-label="Delete selected"', $html);
        $this->assertStringContainsString('ri-delete-bin-2-line', $html);
    }
}
