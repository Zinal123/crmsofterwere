<?php

namespace App\Support;

class StatusBadge
{
    /**
     * Renders the same markup as resources/views/components/ui/status-badge.blade.php,
     * for the few places (like DataTables AJAX JSON payloads) that build HTML as a raw
     * PHP string server-side rather than through a Blade-rendered page. Keep both in
     * sync if either changes.
     */
    public static function render(string $status, string $variant, string $icon): string
    {
        $variantClass = match ($variant) {
            'success' => 'bg-success-subtle text-success',
            'warning' => 'bg-warning-subtle text-warning',
            'danger' => 'bg-danger-subtle text-danger',
            default => 'bg-info-subtle text-info',
        };

        return '<span class="badge ' . $variantClass . ' text-uppercase"><i class="' . e($icon) . ' align-bottom me-1"></i>' . e($status) . '</span>';
    }
}
