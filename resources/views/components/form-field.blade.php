@props(['id', 'label', 'type' => 'text', 'placeholder' => '', 'required' => false, 'hint' => null, 'name' => null])

@php
    $inputName = $name ?? $id;
@endphp

<div class="form-group">
    <label for="{{ $id }}" class="form-label">
        {{ $label }}
        @if($required)
            <span class="required" title="Wajib diisi">*</span>
        @endif
    </label>
    
    @if($type === 'textarea')
        <textarea 
            id="{{ $id }}" 
            name="{{ $inputName }}" 
            class="input-field @error($id) input-error @enderror {{ $attributes->get('class') }}" 
            placeholder="{{ $placeholder }}" 
            {{ $required ? 'required' : '' }}
            {!! $attributes->except(['class', 'name']) !!}>{{ $slot }}</textarea>
    @elseif($type === 'select')
        <select 
            id="{{ $id }}" 
            name="{{ $inputName }}" 
            class="input-field @error($id) input-error @enderror {{ $attributes->get('class') }}" 
            {{ $required ? 'required' : '' }}
            {!! $attributes->except(['class', 'name']) !!}>
            {{ $slot }}
        </select>
    @else
        <input 
            type="{{ $type }}" 
            id="{{ $id }}" 
            name="{{ $inputName }}" 
            class="input-field @error($id) input-error @enderror {{ $attributes->get('class') }}" 
            placeholder="{{ $placeholder }}" 
            {{ $required ? 'required' : '' }}
            {!! $attributes->except(['class', 'name']) !!}
            value="{{ $attributes->get('value') }}">
    @endif

    @error($id)
        <p class="form-error">{{ $message }}</p>
    @else
        @if($hint)
            <p class="form-hint">{{ $hint }}</p>
        @endif
    @enderror
</div>
