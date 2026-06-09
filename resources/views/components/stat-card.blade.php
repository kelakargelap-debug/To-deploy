@props(['label', 'value', 'icon' => null, 'color' => 'accent'])

@php
    $colorMap = [
        'accent' => ['bg' => 'var(--accent-subtle)', 'text' => 'var(--accent)'],
        'success' => ['bg' => 'var(--success-subtle)', 'text' => 'var(--success)'],
        'warning' => ['bg' => 'var(--warning-subtle)', 'text' => 'var(--warning)'],
        'danger' => ['bg' => 'var(--danger-subtle)', 'text' => 'var(--danger)'],
        'info' => ['bg' => 'var(--info-subtle)', 'text' => 'var(--info)'],
        'neutral' => ['bg' => 'var(--bg-subtle)', 'text' => 'var(--text-secondary)'],
    ];
    $c = $colorMap[$color] ?? $colorMap['accent'];
@endphp

<div class="stat-card">
    <div class="flex items-center gap-3 mb-3">
        @if($icon)
            <div class="stat-card-icon" style="background: {{ $c['bg'] }}; color: {{ $c['text'] }};">
                {!! $icon !!}
            </div>
        @endif
        <span class="stat-card-label">{{ $label }}</span>
    </div>
    <div class="stat-card-value" {!! $attributes !!}>{{ $value }}</div>
    @if(isset($slot) && trim($slot) !== '')
        <div class="mt-2">{{ $slot }}</div>
    @endif
</div>
