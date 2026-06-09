@props(['icon' => '📋', 'title', 'description' => null])
<div class="empty-state animate-fade-in-up">
    @if(str_contains($icon, '<svg'))
        <div class="empty-state-icon">
            {!! $icon !!}
        </div>
    @else
        <div class="text-4xl mb-4">{{ $icon }}</div>
    @endif
    <h3 class="empty-state-title">{{ $title }}</h3>
    @if($description)
        <p class="empty-state-desc">{{ $description }}</p>
    @endif
    @if(isset($action) && trim($action) !== '')
        <div class="mt-4">
            {{ $action }}
        </div>
    @endif
</div>