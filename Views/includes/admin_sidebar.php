<?php
/**
 * Views/includes/admin_sidebar.php — menu latéral du Back Office (atelier, Partie 2 — étape 4).
 * Gestion dynamique : lien actif selon $currentPage, et liens affichés selon les permissions
 * de l'utilisateur (le contrôle réel reste fait côté serveur dans chaque contrôleur).
 */
$menu = [
    ['key' => 'dashboard', 'label' => 'Tableau de bord',     'icon' => 'grid',   'url' => url('dashboard'), 'perm' => 'backoffice.access'],
    ['key' => 'users',     'label' => 'Utilisateurs',        'icon' => 'users',  'url' => url('users'),     'perm' => 'users.view'],
    ['key' => 'roles',     'label' => 'Rôles & permissions', 'icon' => 'shield', 'url' => url('roles'),     'perm' => 'roles.manage'],
];
$upcoming = [
    ['label' => 'Signalements',  'icon' => 'pin'],
    ['label' => 'Interventions', 'icon' => 'wrench'],
    ['label' => 'Documents',     'icon' => 'file'],
];
$roleBadge = ['admin' => 'ADMIN', 'agent' => 'AGENT'][$authUser->getRoleNom()] ?? '';
?>
<aside class="cf-sidebar" id="cf-sidebar" aria-label="Menu du Back Office">
    <div class="cf-sidebar__brand">
        <a class="cf-logo cf-logo--light" href="<?= e(url('dashboard')) ?>">
            <span class="cf-logo__mark"><?= icon('logo') ?></span>
            <span>City<span class="cf-logo__accent">Fix</span></span>
        </a>
        <?php if ($roleBadge !== ''): ?>
            <span class="cf-sidebar__badge"><?= e($roleBadge) ?></span>
        <?php endif; ?>
        <button class="cf-sidebar__close cf-btn cf-btn--icon" type="button" aria-label="Fermer le menu" data-sidebar-close>
            <?= icon('x') ?>
        </button>
    </div>

    <nav class="cf-sidebar__nav">
        <span class="cf-sidebar__section">Principal</span>
        <ul>
            <?php foreach ($menu as $item): ?>
                <?php if (!in_array($item['perm'], $permissions, true)) { continue; } ?>
                <li>
                    <a href="<?= e($item['url']) ?>"
                       class="cf-sidebar__link<?= $currentPage === $item['key'] ? ' is-active' : '' ?>"
                       <?= $currentPage === $item['key'] ? 'aria-current="page"' : '' ?>>
                        <?= icon($item['icon']) ?><span><?= e($item['label']) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <span class="cf-sidebar__section">Bientôt</span>
        <ul>
            <?php foreach ($upcoming as $item): ?>
                <li><span class="cf-sidebar__link is-disabled" aria-disabled="true"><?= icon($item['icon']) ?><span><?= e($item['label']) ?></span></span></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="cf-sidebar__footer">
        <a class="cf-sidebar__link" href="<?= e(url('home')) ?>"><?= icon('home') ?><span>Site public</span></a>
        <form method="post" action="<?= e(url('logout')) ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <button type="submit" class="cf-sidebar__link cf-sidebar__logout"><?= icon('logout') ?><span>Déconnexion</span></button>
        </form>
    </div>
</aside>
<div class="cf-sidebar-backdrop" data-sidebar-close hidden></div>
