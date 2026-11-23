<?php
/**
 * Views/admin/users/index.php — liste des utilisateurs avec recherche, filtres et pagination.
 * Variables : $users (UserModel[]), $filters, $total, $page, $pages, $offset, $roles.
 * Les boutons d'action ne s'affichent que si l'utilisateur a la permission
 * (la vérification réelle est faite côté serveur dans UserController).
 */
$can = fn (string $p): bool => in_array($p, $permissions, true);
$isAdmin = $authUser->getRoleNom() === 'admin';
$pageUrl = fn (int $p): string => url('users', array_filter($filters + ['page' => $p], fn ($v) => $v !== '' && $v !== 1));
?>
<div class="cf-page-head">
    <div>
        <nav class="cf-breadcrumb" aria-label="Fil d'Ariane"><a href="<?= e(url('dashboard')) ?>">Back Office</a> › <span aria-current="page">Utilisateurs</span></nav>
        <h1>Gestion des utilisateurs</h1>
        <p class="cf-text-muted cf-mb-0">
            <?= $can('users.edit') ? 'Rechercher, filtrer, créer, modifier, activer ou désactiver les comptes.' : 'Consultation seule — les modifications sont réservées aux administrateurs.' ?>
        </p>
    </div>
    <?php if ($can('users.create')): ?>
        <a class="cf-btn cf-btn--primary" href="<?= e(url('user_create')) ?>"><?= icon('plus', 'cf-icon cf-icon--sm') ?> Nouvel utilisateur</a>
    <?php endif; ?>
</div>

<!-- Filtres -->
<form class="cf-card cf-filters" method="get" action="index.php" role="search" aria-label="Filtrer les utilisateurs">
    <input type="hidden" name="action" value="users">
    <div class="cf-filters__search">
        <label class="cf-sr-only" for="f-q">Nom, prénom ou e-mail</label>
        <?= icon('search', 'cf-icon cf-icon--sm') ?>
        <input class="cf-input" id="f-q" type="search" name="q" maxlength="100" placeholder="Nom, prénom ou e-mail…" value="<?= e($filters['q']) ?>">
    </div>
    <label class="cf-sr-only" for="f-role">Filtrer par rôle</label>
    <select class="cf-select" id="f-role" name="role">
        <option value="">Tous les rôles</option>
        <?php foreach ($roles as $role): ?>
            <option value="<?= e($role->getNom()) ?>" <?= $filters['role'] === $role->getNom() ? 'selected' : '' ?>><?= e($role->getLibelle()) ?></option>
        <?php endforeach; ?>
    </select>
    <label class="cf-sr-only" for="f-statut">Filtrer par statut</label>
    <select class="cf-select" id="f-statut" name="statut">
        <option value="">Tous les statuts</option>
        <option value="actif" <?= $filters['statut'] === 'actif' ? 'selected' : '' ?>>Actif</option>
        <option value="inactif" <?= $filters['statut'] === 'inactif' ? 'selected' : '' ?>>Inactif</option>
    </select>
    <button type="submit" class="cf-btn cf-btn--secondary">Filtrer</button>
    <a class="cf-btn cf-btn--ghost" href="<?= e(url('users')) ?>">Réinitialiser</a>
</form>

<!-- Tableau -->
<div class="cf-card cf-card--flush">
    <div class="cf-table-wrap">
        <table class="cf-table">
            <caption class="cf-sr-only">Liste des utilisateurs (<?= (int) $total ?> résultat<?= $total > 1 ? 's' : '' ?>)</caption>
            <thead>
                <tr>
                    <th scope="col">Utilisateur</th>
                    <th scope="col">Téléphone</th>
                    <th scope="col">Rôle</th>
                    <th scope="col">Statut</th>
                    <th scope="col">Inscription</th>
                    <th scope="col" class="cf-text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($users === []): ?>
                <tr><td colspan="6" class="cf-table__empty">Aucun utilisateur ne correspond à ces critères.</td></tr>
            <?php endif; ?>
            <?php foreach ($users as $u): ?>
                <?php
                $isSelf = $u->getId() === $authUser->getId();
                $canTouch = $isAdmin || $u->getRoleNom() !== 'admin';   // seul un admin agit sur un admin
                ?>
                <tr>
                    <td>
                        <div class="cf-user-cell">
                            <span class="cf-avatar cf-avatar--light" aria-hidden="true"><?= e($u->getInitiales()) ?></span>
                            <div><strong><?= e($u->getNomComplet()) ?></strong><?= $isSelf ? ' <span class="cf-badge cf-badge--neutral">Vous</span>' : '' ?><span class="cf-user-cell__email"><?= e($u->getEmail()) ?></span></div>
                        </div>
                    </td>
                    <td class="cf-text-muted"><?= e($u->getTelephone() ?? '—') ?></td>
                    <td><span class="cf-badge <?= e(role_badge_class($u->getRoleNom())) ?>"><?= e($u->getRoleLibelle()) ?></span></td>
                    <td><span class="cf-status cf-status--<?= e($u->getStatut()) ?>"><?= $u->isActif() ? 'Actif' : 'Inactif' ?></span></td>
                    <td class="cf-text-muted"><?= e(fr_date($u->getCreatedAt())) ?></td>
                    <td>
                        <div class="cf-table__actions">
                            <a class="cf-btn cf-btn--ghost cf-btn--icon" href="<?= e(url('user_show', ['id' => $u->getId()])) ?>" aria-label="Voir le détail de <?= e($u->getNomComplet()) ?>" title="Voir"><?= icon('eye', 'cf-icon cf-icon--sm') ?></a>
                            <?php if ($can('users.edit') && $canTouch): ?>
                                <a class="cf-btn cf-btn--ghost cf-btn--icon cf-action--edit" href="<?= e(url('user_edit', ['id' => $u->getId()])) ?>" aria-label="Modifier <?= e($u->getNomComplet()) ?>" title="Modifier"><?= icon('edit', 'cf-icon cf-icon--sm') ?></a>
                            <?php endif; ?>
                            <?php if ($can('users.toggle') && $canTouch && !$isSelf): ?>
                                <form method="post" action="<?= e(url('user_toggle')) ?>" class="cf-inline-form"
                                      data-confirm="<?= $u->isActif() ? 'Désactiver' : 'Activer' ?> le compte de <?= e($u->getNomComplet()) ?> ?">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $u->getId() ?>">
                                    <button type="submit" class="cf-btn cf-btn--ghost cf-btn--icon cf-action--toggle"
                                            aria-label="<?= $u->isActif() ? 'Désactiver' : 'Activer' ?> <?= e($u->getNomComplet()) ?>" title="<?= $u->isActif() ? 'Désactiver' : 'Activer' ?>">
                                        <?= icon($u->isActif() ? 'ban' : 'check-circle', 'cf-icon cf-icon--sm') ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if ($can('users.delete') && $canTouch && !$isSelf): ?>
                                <form method="post" action="<?= e(url('user_delete')) ?>" class="cf-inline-form"
                                      data-confirm="Supprimer définitivement le compte de <?= e($u->getNomComplet()) ?> ? Cette action est irréversible.">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $u->getId() ?>">
                                    <button type="submit" class="cf-btn cf-btn--ghost cf-btn--icon cf-action--delete" aria-label="Supprimer <?= e($u->getNomComplet()) ?>" title="Supprimer">
                                        <?= icon('trash', 'cf-icon cf-icon--sm') ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="cf-table-footer">
        <span class="cf-text-sm cf-text-muted">
            <?php if ($total > 0): ?>
                Affichage de <?= $offset + 1 ?> à <?= $offset + count($users) ?> sur <?= (int) $total ?> utilisateur<?= $total > 1 ? 's' : '' ?>
            <?php else: ?>
                0 utilisateur
            <?php endif; ?>
        </span>
        <?php if ($pages > 1): ?>
            <nav class="cf-pagination" aria-label="Pagination">
                <?php if ($page > 1): ?><a href="<?= e($pageUrl($page - 1)) ?>" aria-label="Page précédente">‹</a><?php endif; ?>
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <?php if ($p === $page): ?>
                        <span aria-current="page"><?= $p ?></span>
                    <?php else: ?>
                        <a href="<?= e($pageUrl($p)) ?>" aria-label="Page <?= $p ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($page < $pages): ?><a href="<?= e($pageUrl($page + 1)) ?>" aria-label="Page suivante">›</a><?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</div>
