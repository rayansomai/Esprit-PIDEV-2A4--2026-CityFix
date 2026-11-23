/* ==========================================================================
   CITYFIX — admin.js (Back Office)
   Ouverture / fermeture de la sidebar sur tablette et mobile (< 1024 px).
   ========================================================================== */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var sidebar = document.getElementById('cf-sidebar');
        var openBtn = document.querySelector('[data-sidebar-open]');
        var backdrop = document.querySelector('.cf-sidebar-backdrop');
        if (!sidebar || !openBtn) { return; }

        function setOpen(open) {
            sidebar.classList.toggle('is-open', open);
            openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (backdrop) { backdrop.hidden = !open; }
            if (open) {
                var firstLink = sidebar.querySelector('a, button');
                if (firstLink) { firstLink.focus(); }
            } else {
                openBtn.focus();
            }
        }

        openBtn.addEventListener('click', function () { setOpen(true); });
        document.querySelectorAll('[data-sidebar-close]').forEach(function (el) {
            el.addEventListener('click', function () { setOpen(false); });
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && sidebar.classList.contains('is-open')) { setOpen(false); }
        });
    });
})();
