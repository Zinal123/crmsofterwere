@props(['route', 'label' => 'Back'])
<a href="{{ $route }}" {{ $attributes->merge(['class' => 'btn btn-soft-primary mb-3']) }}>
    <i class="ri-arrow-left-line align-bottom me-1"></i> {{ $label }}
</a>
