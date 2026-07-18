{{-- Server-side raw-HTML contexts (e.g. DataTables AJAX payloads) that can't
     use Blade component syntax should mirror this exact output via
     App\Support\StatusBadge::render() - keep both in sync if either changes. --}}
@props(['status', 'variant' => 'info', 'icon' => null])
@php
    if (empty($icon)) {
        throw new \InvalidArgumentException('<x-ui.status-badge> requires an icon prop - status must never be color-only.');
    }
    $variantClass = match($variant ?? 'info') {
        'success' => 'bg-success-subtle text-success',
        'warning' => 'bg-warning-subtle text-warning',
        'danger' => 'bg-danger-subtle text-danger',
        default => 'bg-info-subtle text-info',
    };
@endphp
<span {{ $attributes->merge(['class' => "badge {$variantClass} text-uppercase"]) }}>
    <i class="{{ $icon }} align-bottom me-1"></i>{{ $status }}
</span>
