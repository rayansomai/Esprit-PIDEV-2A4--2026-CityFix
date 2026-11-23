<?php
/**
 * Views/404.php — page introuvable.
 */
?>
<section class="cf-section cf-error-page">
    <div class="cf-container cf-error-page__inner">
        <span class="cf-icon-box cf-icon-box--lg cf-icon-box--navy"><?= icon('pin', 'cf-icon cf-icon--lg') ?></span>
        <p class="cf-error-page__code">404</p>
        <h1>Page introuvable</h1>
        <p class="cf-text-muted">La page demandée n'existe pas ou a été déplacée.</p>
        <div class="cf-row cf-row--center">
            <a class="cf-btn cf-btn--primary" href="<?= e(url('home')) ?>">Retour à l'accueil</a>
        </div>
    </div>
</section>
