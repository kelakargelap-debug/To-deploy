import * as Turbo from "@hotwired/turbo";
Turbo.start();

// Global smooth navigation helper — replaces window.location.href
window.navigateTo = function(path) {
    Turbo.visit(path);
};

// Re-fire DOMContentLoaded-like behavior for inline scripts on Turbo navigation
document.addEventListener('turbo:load', function() {
    // Dispatch a custom event that inline scripts can listen for
    document.dispatchEvent(new CustomEvent('page:load'));
});

// Disable Turbo for the exam page (has onbeforeunload protection)
document.addEventListener('turbo:before-visit', function(event) {
    if (window.location.pathname.includes('/exam')) {
        // If we're ON the exam page, require confirmation before leaving
        if (!confirm('Kamu memiliki ujian yang sedang berjalan. Yakin ingin keluar?')) {
            event.preventDefault();
        }
    }
});

// SKB Tryout Platform - minimal JS entry point
// Page-specific logic is handled inline in each Blade view