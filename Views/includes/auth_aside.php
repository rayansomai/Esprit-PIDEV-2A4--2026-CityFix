<?php
/**
 * Views/includes/auth_aside.php — panneau de présentation affiché à gauche
 * des pages de connexion, d'inscription et de réinitialisation.
 */
?>
<div class="cf-auth__aside">
    <h2>Ensemble, rendons la ville plus agréable.</h2>
    <p>Un seul compte pour signaler, suivre vos demandes et échanger avec les services municipaux.</p>
    <ul class="cf-auth__points">
        <li><?= icon('check') ?> Mots de passe chiffrés (hachage bcrypt)</li>
        <li><?= icon('check') ?> Session protégée et déconnexion automatique</li>
        <li><?= icon('check') ?> Vos données restent privées</li>
    </ul>
</div>
