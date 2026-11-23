<?php
/**
 * Views/admin/roles/index.php — rôles prédéfinis et matrice des permissions (administrateur).
 * Variables : $roles (RoleModel[]), $permissionList (PermissionModel[]), $matrix [role_id => [permission_id]].
 */
$roleAccent = ['citoyen' => 'cf-role-card--citoyen', 'agent' => 'cf-role-card--agent', 'admin' => 'cf-role-card--admin'];
?>
<div class="cf-page-head">
    <div>
        <nav class="cf-breadcrumb" aria-label="Fil d'Ariane"><a href="<?= e(url('dashboard')) ?>">Back Office</a> › <span aria-current="page">Rôles &amp; permissions</span></nav>
        <h1>Rôles &amp; permissions</h1>
        <p class="cf-text-muted cf-mb-0">Les trois rôles sont prédéfinis. Cochez les permissions attribuées à chaque rôle. Le rôle d'un utilisateur se change depuis sa fiche.</p>
    </div>
</div>

<div class="cf-stats cf-stats--3">
    <?php foreach ($roles as $role): ?>
        <div class="cf-card cf-role-card <?= e($roleAccent[$role->getNom()] ?? '') ?>">
            <div class="cf-card__header">
                <h2 class="cf-card__title"><?= e($role->getLibelle()) ?></h2>
                <span class="cf-badge <?= e(role_badge_class($role->getNom())) ?>"><?= (int) $role->getNbUsers() ?> compte<?= $role->getNbUsers() > 1 ? 's' : '' ?></span>
            </div>
            <p class="cf-text-sm cf-text-muted cf-mb-0"><?= e($role->getDescription()) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<form method="post" action="<?= e(url('roles_update')) ?>" class="cf-card cf-card--flush" data-confirm="Enregistrer les nouvelles permissions ?">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
    <div class="cf-card__header cf-card__header--padded">
        <h2 class="cf-card__title">Matrice des permissions — Module 1</h2>
        <button type="submit" class="cf-btn cf-btn--primary">Enregistrer</button>
    </div>
    <div class="cf-table-wrap">
        <table class="cf-table cf-matrix">
            <caption class="cf-sr-only">Permissions attribuées à chaque rôle</caption>
            <thead>
                <tr>
                    <th scope="col">Permission</th>
                    <?php foreach ($roles as $role): ?>
                        <th scope="col" class="cf-text-center"><?= e($role->getLibelle()) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($permissionList as $perm): ?>
                <tr>
                    <th scope="row">
                        <span class="cf-matrix__label"><?= e($perm->getLibelle()) ?></span>
                        <code class="cf-matrix__key"><?= e($perm->getCle()) ?></code>
                        <?php if ($perm->isReserveAdmin()): ?><span class="cf-badge cf-badge--admin">Réservée à l'administrateur</span><?php endif; ?>
                    </th>
                    <?php foreach ($roles as $role): ?>
                        <?php
                        $locked  = $role->getNom() === 'admin' || $perm->isReserveAdmin();
                        $checked = $role->getNom() === 'admin' || in_array($perm->getId(), $matrix[$role->getId()] ?? [], true);
                        $inputId = 'perm-' . $role->getId() . '-' . $perm->getId();
                        ?>
                        <td class="cf-text-center">
                            <label class="cf-matrix__check" for="<?= e($inputId) ?>">
                                <span class="cf-sr-only"><?= e($perm->getLibelle()) ?> pour <?= e($role->getLibelle()) ?></span>
                                <input type="checkbox" id="<?= e($inputId) ?>"
                                       name="perms[<?= (int) $role->getId() ?>][]" value="<?= e($perm->getCle()) ?>"
                                       <?= $checked ? 'checked' : '' ?> <?= $locked ? 'disabled' : '' ?>>
                            </label>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="cf-matrix__note cf-text-sm cf-text-muted">
        <?= icon('lock', 'cf-icon cf-icon--sm') ?>
        Les permissions de l'administrateur sont verrouillées. Attribuer une action de gestion (créer, modifier, activer, supprimer) ajoute automatiquement « Voir les utilisateurs » et « Accéder au Back Office ».
        Un rôle non administrateur ne peut jamais agir sur un compte administrateur.
    </p>
</form>
