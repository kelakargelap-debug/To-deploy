@props(['id', 'title'])
<div id="{{ $id }}" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('{{ $id }}').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
            <button onclick="document.getElementById('{{ $id }}').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        {{ $slot }}
    </div>
</div>