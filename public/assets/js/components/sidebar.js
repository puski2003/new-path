/**
 * sidebar.js — mobile sidebar toggle
 * Works with the `.sidebar` + `.sidebar--open` CSS classes.
 */
document.addEventListener('DOMContentLoaded', () => {
    const sidebar  = document.querySelector('.sidebar');
    const toggle   = document.querySelector('.sidebar-toggle');
    const overlay  = document.querySelector('.sidebar-overlay');

    if (!sidebar || !toggle) return;

    function openSidebar() {
        sidebar.classList.add('sidebar--open');
        overlay?.classList.add('sidebar-overlay--visible');
    }

    function closeSidebar() {
        sidebar.classList.remove('sidebar--open');
        overlay?.classList.remove('sidebar-overlay--visible');
    }

    toggle.addEventListener('click', () => {
        if (sidebar.classList.contains('sidebar--open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    overlay?.addEventListener('click', closeSidebar);

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth > 768) return;
        if (!sidebar.contains(e.target) && e.target !== toggle) {
            closeSidebar();
        }
    });
});
