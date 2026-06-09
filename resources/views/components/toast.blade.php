{{-- Toast notification container --}}
<div id="toast-container" class="toast-container"></div>

{{-- Show toasts from PHP session --}}
@if(session('success'))
    <script>document.addEventListener('DOMContentLoaded', function() { showToast(@json(session('success')), 'success'); });</script>
@endif
@if(session('error'))
    <script>document.addEventListener('DOMContentLoaded', function() { showToast(@json(session('error')), 'error'); });</script>
@endif
@if(session('warning'))
    <script>document.addEventListener('DOMContentLoaded', function() { showToast(@json(session('warning')), 'warning'); });</script>
@endif
@if(session('info'))
    <script>document.addEventListener('DOMContentLoaded', function() { showToast(@json(session('info')), 'info'); });</script>
@endif

<script>
function showToast(message, type, duration) {
    type = type || 'info';
    duration = duration || 4000;
    var container = document.getElementById('toast-container');
    if (!container) return;

    var icons = {
        success: '<svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        error: '<svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>',
        warning: '<svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>',
        info: '<svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>'
    };

    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML = (icons[type] || icons.info) +
        '<span class="flex-1 min-w-0">' + message + '</span>' +
        '<button class="toast-dismiss" onclick="dismissToast(this.parentElement)">' +
        '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
        '</button>';

    container.appendChild(toast);

    // Auto-dismiss
    setTimeout(function() { dismissToast(toast); }, duration);
}

function dismissToast(el) {
    if (!el || el.classList.contains('toast-exit')) return;
    el.classList.add('toast-exit');
    setTimeout(function() { if (el.parentNode) el.parentNode.removeChild(el); }, 200);
}
</script>
