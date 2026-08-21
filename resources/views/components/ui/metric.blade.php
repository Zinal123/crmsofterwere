@props(['label', 'value', 'icon' => null, 'color' => 'primary', 'link' => null, 'trend' => null])
<div {{ $attributes->merge(['class' => 'card dash-card']) }}>
    <div class="card-body d-flex flex-column justify-content-center h-100">
        <div class="d-flex justify-content-between align-items-start">
            <div class="overflow-hidden">
                <p class="text-muted mb-1 small text-uppercase text-truncate">{{ $label }}</p>
                <h4 class="mb-0 tabular-nums text-{{ $color }}">{{ $value }}</h4>
                @if($trend !== null)
                    @php
                        $trendUp = $trend >= 0;
                    @endphp
                    <span class="badge bg-{{ $trendUp ? 'success' : 'danger' }}-subtle text-{{ $trendUp ? 'success' : 'danger' }} mt-1">
                        <i class="ri-arrow-{{ $trendUp ? 'up' : 'down' }}-line align-bottom"></i> {{ number_format(abs($trend), 1) }}% vs last month
                    </span>
                @endif
                @if($link)<a href="{{ $link }}" class="small text-decoration-underline d-block mt-1">Details</a>@endif
            </div>
            @if($icon)<span class="stat-icon bg-{{ $color }}-subtle text-{{ $color }}"><i class="{{ $icon }}"></i></span>@endif
        </div>
        @isset($description)
            <p class="text-muted small mb-0 mt-3">{{ $description }}</p>
        @endisset
    </div>
</div>
