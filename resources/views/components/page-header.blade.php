@props(['title', 'subtitle' => null, 'icon' => null])
<div class="page-header animate-fade-in-up">
    <div>
        @if(isset($breadcrumb) && trim($breadcrumb) !== '')
            <div class="breadcrumb">
                {{ $breadcrumb }}
            </div>
        @endif
        
        <div class="flex items-center gap-3">
            @if($icon)
                <div class="text-[var(--text-secondary)]">
                    {!! $icon !!}
                </div>
            @endif
            <h1 class="page-header-title">{{ $title }}</h1>
        </div>
        
        @if($subtitle)
            <p class="page-header-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($slot) && trim($slot) !== '')
        <div class="flex items-center gap-2 mt-4 md:mt-0">
            {{ $slot }}
        </div>
    @endif
</div>