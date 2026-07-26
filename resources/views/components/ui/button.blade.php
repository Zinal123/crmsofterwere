@php
    $prefix = ($soft ?? false) ? 'btn-soft-' : 'btn-';
    $variantClass = match($variant ?? 'primary') {
        'success' => $prefix . 'success',
        'danger' => $prefix . 'danger',
        'secondary' => $prefix . 'secondary',
        'info' => $prefix . 'info',
        'warning' => $prefix . 'warning',
        default => $prefix . 'primary',
    };
    $sizeClass = match($size ?? null) {
        'sm' => 'btn-sm',
        default => '',
    };
    $isIconOnly = isset($icon) && trim($slot) === '';
    if ($isIconOnly && empty($ariaLabel)) {
        throw new \InvalidArgumentException('<x-ui.button> with an icon and no visible label text must have an ariaLabel attribute.');
    }
@endphp
<button
    {{ $attributes->except(['variant', 'size', 'icon', 'ariaLabel', 'soft'])->merge(['type' => 'button', 'class' => trim("btn {$variantClass} {$sizeClass}")]) }}
    @if($isIconOnly) aria-label="{{ $ariaLabel }}" @endif
>
    @isset($icon)
        <i class="{{ $icon }} @if(trim($slot) !== '') align-bottom me-1 @endif"></i>
    @endisset
    {{ $slot }}
</button>
