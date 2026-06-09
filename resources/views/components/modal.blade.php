@props(['id', 'title', 'size' => 'md'])

<div id="{{ $id }}" class="modal-overlay hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0" onclick="document.getElementById('{{ $id }}').classList.add('hidden')"></div>
    <div class="modal-content modal-{{ $size }}">
        <div class="modal-header">
            <h3 class="modal-title">{{ $title }}</h3>
            <button onclick="document.getElementById('{{ $id }}').classList.add('hidden')" class="modal-close" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            {{ $slot }}
        </div>
        @if(isset($footer) && trim($footer) !== '')
            <div class="modal-footer">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>