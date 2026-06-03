// Copy protection for exam and material views
function enableCopyProtection() {
    document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
    document.addEventListener('copy', function(e) { e.preventDefault(); });
    document.addEventListener('cut', function(e) { e.preventDefault(); });
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && (e.key === 'c' || e.key === 'a' || e.key === 'u' || e.key === 'x')) {
            e.preventDefault();
        }
        if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
            e.preventDefault();
        }
    });
}