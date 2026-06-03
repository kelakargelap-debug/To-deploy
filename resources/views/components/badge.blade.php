@props(['type' => 'default', 'text' => ''])

@php
    $classes = [
        'premium' => 'badge badge-premium',
        'free' => 'badge badge-free',
        'success' => 'badge badge-success',
        'danger' => 'badge badge-danger',
        'warning' => 'badge badge-warning',
        'info' => 'badge badge-info',
        'draft' => 'badge badge-draft',
        'published' => 'badge badge-published',
        'archived' => 'badge badge-archived',
        'active' => 'badge badge-active',
        'expired' => 'badge badge-expired',
        'suspended' => 'badge badge-suspended',
        'default' => 'badge',
    ][$type] ?? 'badge';
@endphp

<span class="{{ $classes }}">{{ $text ?? $slot }}</span>