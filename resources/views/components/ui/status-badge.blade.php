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
