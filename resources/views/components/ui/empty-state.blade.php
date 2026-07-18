<div class="text-center py-5">
    <i class="{{ $icon }} display-4 text-muted d-block mb-3"></i>
    <p class="text-muted fs-15 mb-3">{{ $message }}</p>
    @isset($slot)
        {{ $slot }}
    @endisset
</div>
