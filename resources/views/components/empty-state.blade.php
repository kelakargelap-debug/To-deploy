@props(['icon' => '📋', 'title', 'description' => null])
<div class="text-center py-12">
    <div class="text-4xl mb-4">{{ $icon }}</div>
    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $title }}</h3>
    @if($description)
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ $description }}</p>
    @endif
</div>