<?php
/**
 * Views/403.php — accès refusé (permission insuffisante).
 */
?>
<section class="cf-section cf-error-page">
    <div class="cf-container cf-error-page__inner">
        <span class="cf-icon-box cf-icon-box--lg cf-icon-box--red"><?= icon('lock', 'cf-icon cf-icon--lg') ?></span>
        <p class="cf-error-page__code">403</p>
        <h1>Accès refusé</h1>
        <p class="cf-text-muted">Vous n'avez pas les permissions nécessaires pour accéder à cette page.</p>
        <div class="cf-row cf-row--center">
            <a class="cf-btn cf-btn--primary" href="<?= e(url('home')) ?>">Retour à l'accueil</a>
            <?php if ($authUser !== null): ?>
                <a class="cf-btn cf-btn--outline" href="<?= e(url('profile')) ?>">Mon espace</a>
            <?php endif; ?>
        </div>
    </div>
</section>
