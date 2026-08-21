@props(['checked' => false, 'name' => 'is_active', 'ariaLabel', 'formId' => null])
<label class="oms-toggle">
    <input type="hidden" name="{{ $name }}" value="0" @if($formId) form="{{ $formId }}" @endif>
    <input
        {{ $attributes->merge(['class' => '']) }}
        type="checkbox"
        role="switch"
        name="{{ $name }}"
        value="1"
        @checked($checked)
        @if($formId) form="{{ $formId }}" @endif
        onchange="this.form.submit()"
        aria-label="{{ $ariaLabel }}"
    >
    <span class="oms-toggle-track"><span class="oms-toggle-thumb"></span></span>
</label>
