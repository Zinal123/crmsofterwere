@props(['title', 'createRoute' => null, 'createLabel' => null])
<div class="card">
    <div class="card-header border-0">
        <div class="d-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">{{ $title }}</h5>
            @if($createRoute && $createLabel)
                <div class="flex-shrink-0">
                    <a href="{{ $createRoute }}" class="btn btn-success"><i class="ri-add-line align-bottom me-1"></i> {{ $createLabel }}</a>
                </div>
            @endif
        </div>
    </div>
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
