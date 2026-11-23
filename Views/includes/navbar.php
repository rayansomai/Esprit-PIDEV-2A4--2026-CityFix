<?php
/**
 * Views/includes/navbar.php — barre de navigation du Front Office (atelier, étape 5).
 * Gestion dynamique : la classe « is-active » est ajoutée au lien de la page courante
 * en comparant $currentPage (définie par le contrôleur) au nom de chaque lien.
 */
$navLinks = [
    'home'  => ['label' => 'Accueil',  'url' => url('home')],
    'about' => ['label' => 'À propos', 'url' => url('about')],
];
if ($authUser !== null && in_array('profile.manage', $permissions, true)) {
    $navLinks['profile'] = ['label' => 'Mon espace', 'url' => url('profile')];
}
?>
<header class="cf-nav">
    <div class="cf-container cf-nav__inner">
        <a class="cf-logo" href="<?= e(url('home')) ?>" aria-label="CITYFIX — accueil">
            <span class="cf-logo__mark"><?= icon('logo') ?></span>
            <span>City<span class="cf-logo__accent">Fix</span></span>
        </a>

        <button class="cf-btn cf-btn--ghost cf-btn--icon cf-nav__toggle" type="button"
                aria-controls="cf-nav-menu" aria-expanded="false" aria-label="Ouvrir le menu" data-nav-toggle>
            <?= icon('menu') ?>
        </button>

        <div class="cf-nav__menu" id="cf-nav-menu">
            <nav aria-label="Navigation principale">
                <ul class="cf-nav__links">
                    <?php foreach ($navLinks as $key => $link): ?>
                        <li>
                            <a href="<?= e($link['url']) ?>"
                               class="cf-nav__link<?= $currentPage === $key ? ' is-active' : '' ?>"
                               <?= $currentPage === $key ? 'aria-current="page"' : '' ?>>
                                <?= e($link['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="cf-nav__actions">
                <?php if ($authUser !== null): ?>
                    <?php if (in_array('backoffice.access', $permissions, true)): ?>
                        <a class="cf-btn cf-btn--outline" href="<?= e(url('dashboard')) ?>"><?= icon('grid', 'cf-icon cf-icon--sm') ?> Back Office</a>
                    <?php endif; ?>
                    <span class="cf-nav__user">
                        <span class="cf-avatar cf-avatar--soft" aria-hidden="true"><?= e($authUser->getInitiales()) ?></span>
                        <span class="cf-nav__username"><?= e($authUser->getPrenom()) ?></span>
                    </span>
                    <form method="post" action="<?= e(url('logout')) ?>" class="cf-inline-form">
                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                        <button type="submit" class="cf-btn cf-btn--outline cf-btn--icon" aria-label="Se déconnecter" title="Se déconnecter">
                            <?= icon('logout', 'cf-icon cf-icon--sm') ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a class="cf-btn cf-btn--ghost<?= $currentPage === 'login' ? ' is-active' : '' ?>" href="<?= e(url('login')) ?>">Connexion</a>
                    <a class="cf-btn cf-btn--primary" href="<?= e(url('register')) ?>">Créer un compte</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
