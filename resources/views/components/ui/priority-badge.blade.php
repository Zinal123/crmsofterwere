{{-- Single source of truth for priority→color/icon mapping, consumed by
     every ticket/problem-type view that shows a priority. Do not duplicate
     this match() inline in a view again - two prior copies disagreed with
     each other (medium→secondary/low→light vs medium→info/low→success). --}}
@props(['priority'])
@php
    $variant = match($priority) {
        'urgent' => 'danger',
        'high' => 'warning',
        'low' => 'light',
        default => 'secondary', // medium, or unset
    };
    $icon = match($priority) {
        'urgent' => 'ri-alarm-warning-line',
        'high' => 'ri-arrow-up-circle-line',
        'low' => 'ri-arrow-down-circle-line',
        default => 'ri-subtract-line',
    };
@endphp
<x-ui.status-badge :status="ucfirst($priority ?? 'Medium')" :variant="$variant" :icon="$icon" />
