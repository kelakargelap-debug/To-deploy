@props(['id', 'label', 'type' => 'text', 'placeholder' => '', 'required' => false, 'hint' => null])

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
            name="{{ $id }}" 
            class="input-field @error($id) input-error @enderror {{ $attributes->get('class') }}" 
            placeholder="{{ $placeholder }}" 
            {{ $required ? 'required' : '' }}
            {!! $attributes->except('class') !!}>{{ $slot }}</textarea>
    @elseif($type === 'select')
        <select 
            id="{{ $id }}" 
            name="{{ $id }}" 
            class="input-field @error($id) input-error @enderror {{ $attributes->get('class') }}" 
            {{ $required ? 'required' : '' }}
            {!! $attributes->except('class') !!}>
            {{ $slot }}
        </select>
    @else
        <input 
            type="{{ $type }}" 
            id="{{ $id }}" 
            name="{{ $id }}" 
            class="input-field @error($id) input-error @enderror {{ $attributes->get('class') }}" 
            placeholder="{{ $placeholder }}" 
            {{ $required ? 'required' : '' }}
            {!! $attributes->except('class') !!}
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
