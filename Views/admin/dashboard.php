<?php
/**
 * Views/admin/dashboard.php — tableau de bord du Back Office.
 * Variables : $total, $byRole, $inactifs, $latest (UserModel[]).
 */
$canView   = in_array('users.view', $permissions, true);
$canCreate = in_array('users.create', $permissions, true);
$maxRole = max(1, $total);
?>
<div class="cf-page-head">
    <div>
        <h1>Tableau de bord</h1>
        <p class="cf-text-muted cf-mb-0">Bonjour <?= e($authUser->getPrenom()) ?>, voici la vue d'ensemble des comptes de la plateforme.</p>
    </div>
    <?php if ($canCreate): ?>
        <a class="cf-btn cf-btn--primary" href="<?= e(url('user_create')) ?>"><?= icon('plus', 'cf-icon cf-icon--sm') ?> Ajouter un utilisateur</a>
    <?php endif; ?>
</div>

<div class="cf-stats">
    <div class="cf-card cf-card--hover cf-stat">
        <div class="cf-stat__top"><span class="cf-stat__label">Utilisateurs</span><span class="cf-icon-box cf-icon-box--navy"><?= icon('users') ?></span></div>
        <span class="cf-stat__value"><?= (int) $total ?></span>
    </div>
    <div class="cf-card cf-card--hover cf-stat">
        <div class="cf-stat__top"><span class="cf-stat__label">Citoyens</span><span class="cf-icon-box cf-icon-box--green"><?= icon('user') ?></span></div>
        <span class="cf-stat__value"><?= (int) $byRole['citoyen'] ?></span>
    </div>
    <div class="cf-card cf-card--hover cf-stat">
        <div class="cf-stat__top"><span class="cf-stat__label">Agents municipaux</span><span class="cf-icon-box cf-icon-box--blue"><?= icon('wrench') ?></span></div>
        <span class="cf-stat__value"><?= (int) $byRole['agent'] ?></span>
    </div>
    <div class="cf-card cf-card--hover cf-stat">
        <div class="cf-stat__top"><span class="cf-stat__label">Comptes inactifs</span><span class="cf-icon-box cf-icon-box--red"><?= icon('ban') ?></span></div>
        <span class="cf-stat__value"><?= (int) $inactifs ?></span>
    </div>
</div>

<div class="cf-dash-grid">
    <section class="cf-card" aria-labelledby="repartition-title">
        <h2 class="cf-card__title" id="repartition-title">Répartition par rôle</h2>
        <div class="cf-bar" role="img" aria-label="Citoyens : <?= (int) $byRole['citoyen'] ?>, agents : <?= (int) $byRole['agent'] ?>, administrateurs : <?= (int) $byRole['admin'] ?>">
            <span class="cf-bar__seg cf-bar__seg--citoyen" style="flex-grow: <?= (int) $byRole['citoyen'] ?>"></span>
            <span class="cf-bar__seg cf-bar__seg--agent" style="flex-grow: <?= (int) $byRole['agent'] ?>"></span>
            <span class="cf-bar__seg cf-bar__seg--admin" style="flex-grow: <?= (int) $byRole['admin'] ?>"></span>
        </div>
        <ul class="cf-legend">
            <li><span class="cf-legend__dot cf-bar__seg--citoyen"></span>Citoyens<strong><?= (int) $byRole['citoyen'] ?></strong></li>
            <li><span class="cf-legend__dot cf-bar__seg--agent"></span>Agents municipaux<strong><?= (int) $byRole['agent'] ?></strong></li>
            <li><span class="cf-legend__dot cf-bar__seg--admin"></span>Administrateurs<strong><?= (int) $byRole['admin'] ?></strong></li>
        </ul>
    </section>

    <section class="cf-card" aria-labelledby="latest-title">
        <div class="cf-card__header">
            <h2 class="cf-card__title" id="latest-title">Derniers inscrits</h2>
            <?php if ($canView): ?><a class="cf-text-sm" href="<?= e(url('users')) ?>">Voir tout →</a><?php endif; ?>
        </div>
        <?php if (!$canView): ?>
            <p class="cf-text-muted cf-mb-0">Vous n'avez pas la permission de consulter la liste des utilisateurs.</p>
        <?php elseif ($latest === []): ?>
            <p class="cf-text-muted cf-mb-0">Aucun utilisateur.</p>
        <?php else: ?>
            <div class="cf-table-wrap">
                <table class="cf-table cf-table--compact">
                    <thead><tr><th scope="col">Utilisateur</th><th scope="col">Rôle</th><th scope="col">Statut</th><th scope="col">Inscription</th></tr></thead>
                    <tbody>
                    <?php foreach ($latest as $u): ?>
                        <tr>
                            <td><a class="cf-user-link" href="<?= e(url('user_show', ['id' => $u->getId()])) ?>"><strong><?= e($u->getNomComplet()) ?></strong><span><?= e($u->getEmail()) ?></span></a></td>
                            <td><span class="cf-badge <?= e(role_badge_class($u->getRoleNom())) ?>"><?= e($u->getRoleLibelle()) ?></span></td>
                            <td><span class="cf-status cf-status--<?= e($u->getStatut()) ?>"><?= $u->isActif() ? 'Actif' : 'Inactif' ?></span></td>
                            <td class="cf-text-muted"><?= e(fr_date($u->getCreatedAt())) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
