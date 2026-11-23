<?php
/**
 * Views/includes/admin_header.php — barre du haut du Back Office (atelier, Partie 2 — étape 5) :
 * bouton menu (mobile), recherche rapide d'utilisateurs, profil connecté.
 */
?>
<header class="cf-topbar">
    <button class="cf-btn cf-btn--outline cf-btn--icon cf-topbar__menu" type="button"
            aria-controls="cf-sidebar" aria-expanded="false" aria-label="Ouvrir le menu" data-sidebar-open>
        <?= icon('menu') ?>
    </button>

    <?php if (in_array('users.view', $permissions, true)): ?>
        <form class="cf-topbar__search" method="get" action="index.php" role="search">
            <input type="hidden" name="action" value="users">
            <label class="cf-sr-only" for="cf-quick-search">Rechercher un utilisateur</label>
            <?= icon('search', 'cf-icon cf-icon--sm') ?>
            <input id="cf-quick-search" type="search" name="q" placeholder="Rechercher un utilisateur…" maxlength="100">
        </form>
    <?php else: ?>
        <span></span>
    <?php endif; ?>

    <div class="cf-topbar__user">
        <span class="cf-avatar" aria-hidden="true"><?= e($authUser->getInitiales()) ?></span>
        <span class="cf-topbar__identity">
            <span class="cf-topbar__name"><?= e($authUser->getNomComplet()) ?></span>
            <span class="cf-topbar__role"><?= e($authUser->getRoleLibelle()) ?></span>
        </span>
        <a class="cf-btn cf-btn--ghost cf-btn--icon" href="<?= e(url('profile')) ?>" aria-label="Mon profil" title="Mon profil"><?= icon('user', 'cf-icon cf-icon--sm') ?></a>
    </div>
</header>
