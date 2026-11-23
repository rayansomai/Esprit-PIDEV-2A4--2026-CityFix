/* ==========================================================================
   CITYFIX — validation.js
   Validation CÔTÉ CLIENT des formulaires marqués data-validate.
   Elle améliore le confort (erreurs immédiates) mais ne remplace jamais
   la validation côté serveur (PHP), toujours exécutée.

   Règles lues dans le HTML :
     required, minlength, maxlength, type="email"
     data-rule="name"      lettres, espaces, apostrophes, tirets (2 à 50)
     data-rule="phone"     8 à 15 chiffres (facultatif)
     data-rule="password"  8 à 72 caractères, majuscule, minuscule, chiffre
     data-match="#id"      doit être identique au champ indiqué
     data-strength="#id"   indicateur de force du mot de passe
   ========================================================================== */
(function () {
    'use strict';

    var NAME_RE = /^[A-Za-zÀ-ÖØ-öø-ÿ][A-Za-zÀ-ÖØ-öø-ÿ\s'\-]{1,49}$/;
    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    var PHONE_RE = /^\+?[0-9]{8,15}$/;

    function passwordScore(value) {
        var score = 0;
        if (value.length >= 8) { score++; }
        if (/[A-Z]/.test(value) && /[a-z]/.test(value)) { score++; }
        if (/[0-9]/.test(value)) { score++; }
        if (value.length >= 12 || /[^A-Za-z0-9]/.test(value)) { score++; }
        return score;
    }

    /** Retourne le message d'erreur d'un champ, ou une chaîne vide s'il est valide. */
    function validateField(input) {
        if (input.disabled || input.type === 'hidden') { return ''; }
        var value = input.type === 'password' ? input.value : input.value.trim();
        var rule = input.getAttribute('data-rule');

        if (input.type === 'radio') {
            var group = input.form.querySelectorAll('input[type="radio"][name="' + input.name + '"]');
            var checked = Array.prototype.some.call(group, function (r) { return r.checked; });
            return input.required && !checked ? 'Veuillez faire un choix.' : '';
        }
        if (input.required && value === '') {
            return 'Ce champ est obligatoire.';
        }
        if (value === '') { return ''; }

        if (input.type === 'email' && (!EMAIL_RE.test(value) || value.length > 150)) {
            return "Format d'adresse e-mail invalide.";
        }
        if (rule === 'name') {
            if (value.length < 2 || value.length > 50) { return 'Entre 2 et 50 caractères.'; }
            if (!NAME_RE.test(value)) { return 'Lettres, espaces, apostrophes et tirets uniquement.'; }
        }
        if (rule === 'phone' && !PHONE_RE.test(value.replace(/\s+/g, ''))) {
            return 'Entre 8 et 15 chiffres.';
        }
        if (rule === 'password') {
            if (value.length < 8 || value.length > 72) { return 'Entre 8 et 72 caractères.'; }
            if (!/[A-Z]/.test(value) || !/[a-z]/.test(value) || !/[0-9]/.test(value)) {
                return 'Au moins une majuscule, une minuscule et un chiffre.';
            }
        }
        var match = input.getAttribute('data-match');
        if (match) {
            var other = document.querySelector(match);
            if (other && other.value !== input.value) { return 'Les deux mots de passe ne correspondent pas.'; }
        }
        var min = parseInt(input.getAttribute('minlength') || '0', 10);
        var max = parseInt(input.getAttribute('maxlength') || '0', 10);
        if (min && value.length < min) { return 'Au moins ' + min + ' caractères.'; }
        if (max && value.length > max) { return 'Au maximum ' + max + ' caractères.'; }
        return '';
    }

    /** Affiche (ou efface) l'erreur sous le champ, avec les attributs d'accessibilité. */
    function showError(input, message) {
        var errorEl = document.getElementById(input.id + '-error')
            || (input.name === 'role_id' ? document.getElementById('role_id-error') : null);
        if (input.type !== 'radio') {
            input.classList.toggle('is-invalid', message !== '');
            input.setAttribute('aria-invalid', message !== '' ? 'true' : 'false');
        }
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.hidden = message === '';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-validate]').forEach(function (form) {
            var fields = form.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), select, textarea');

            fields.forEach(function (input) {
                // Vérification quand on quitte le champ, puis en direct une fois l'erreur affichée
                input.addEventListener('blur', function () { showError(input, validateField(input)); });
                input.addEventListener('input', function () {
                    if (input.classList.contains('is-invalid')) { showError(input, validateField(input)); }
                });

                // Indicateur de force du mot de passe
                var strengthSel = input.getAttribute('data-strength');
                if (strengthSel) {
                    var bars = document.querySelectorAll(strengthSel + ' span');
                    input.addEventListener('input', function () {
                        var score = passwordScore(input.value);
                        bars.forEach(function (bar, i) { bar.classList.toggle('is-on', i < score); });
                    });
                }
            });

            form.addEventListener('submit', function (event) {
                var firstInvalid = null;
                fields.forEach(function (input) {
                    var message = validateField(input);
                    showError(input, message);
                    if (message && !firstInvalid) { firstInvalid = input; }
                });
                if (firstInvalid) {
                    event.preventDefault();
                    firstInvalid.focus();
                }
            });
        });
    });
})();
