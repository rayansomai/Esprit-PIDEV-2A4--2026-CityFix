/* ==========================================================================
   CITYFIX — main.js (Front Office et Back Office)
   - masque le spinner une fois la page chargée ;
   - menu mobile de la navbar ;
   - bouton « Retour en haut » ;
   - confirmation avant les actions sensibles (formulaires data-confirm).
   Aucun script en ligne dans le HTML (Content-Security-Policy).
   ========================================================================== */
(function () {
    'use strict';

    // Spinner (atelier, étape 4)
    window.addEventListener('load', function () {
        var spinner = document.getElementById('cf-spinner');
        if (spinner) {
            spinner.classList.add('is-hidden');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Menu mobile de la navbar
        var toggle = document.querySelector('[data-nav-toggle]');
        var menu = document.getElementById('cf-nav-menu');
        if (toggle && menu) {
            toggle.addEventListener('click', function () {
                var open = menu.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && menu.classList.contains('is-open')) {
                    menu.classList.remove('is-open');
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.focus();
                }
            });
        }

        // Bouton « Retour en haut »
        var backToTop = document.querySelector('[data-back-to-top]');
        if (backToTop) {
            var onScroll = function () {
                backToTop.classList.toggle('is-visible', window.scrollY > 400);
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }

        // Confirmation des actions sensibles (désactivation, suppression, permissions)
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!window.confirm(form.getAttribute('data-confirm'))) {
                    event.preventDefault();
                }
            });
        });
    });
})();
