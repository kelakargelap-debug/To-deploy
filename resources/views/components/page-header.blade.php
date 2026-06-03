@props(['title', 'subtitle' => null])
<div class="page-header">
    <div>
        <h1 class="text-[1.333rem] font-medium tracking-[-0.03em]" style="color: var(--text-primary);">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-[13px] mt-1" style="color: var(--text-secondary);">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($slot) && trim($slot) !== '')
        <div class="flex items-center gap-2 mt-3 md:mt-0">
            {{ $slot }}
        </div>
    @endif
</div>