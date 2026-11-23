<?php
/**
 * Views/admin/users/show.php — fiche détaillée d'un utilisateur.
 * Variables : $user, $isLastAdmin, $isSelf.
 */
$can = fn (string $p): bool => in_array($p, $permissions, true);
$canTouch = $authUser->getRoleNom() === 'admin' || $user->getRoleNom() !== 'admin';
?>
<div class="cf-page-head">
    <div>
        <nav class="cf-breadcrumb" aria-label="Fil d'Ariane"><a href="<?= e(url('dashboard')) ?>">Back Office</a> › <a href="<?= e(url('users')) ?>">Utilisateurs</a> › <span aria-current="page">Détail</span></nav>
        <h1><?= e($user->getNomComplet()) ?></h1>
    </div>
    <div class="cf-row">
        <a class="cf-btn cf-btn--outline" href="<?= e(url('users')) ?>"><?= icon('arrow-left', 'cf-icon cf-icon--sm') ?> Retour à la liste</a>
        <?php if ($can('users.edit') && $canTouch): ?>
            <a class="cf-btn cf-btn--primary" href="<?= e(url('user_edit', ['id' => $user->getId()])) ?>"><?= icon('edit', 'cf-icon cf-icon--sm') ?> Modifier</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($isLastAdmin): ?>
    <div class="cf-alert cf-alert--warning cf-mb-4" role="status"><strong>Dernier administrateur actif.</strong> Son rôle, son statut et sa suppression sont verrouillés.</div>
<?php endif; ?>

<div class="cf-detail-grid">
    <section class="cf-card cf-user-card">
        <span class="cf-avatar cf-avatar--lg" aria-hidden="true"><?= e($user->getInitiales()) ?></span>
        <h2><?= e($user->getNomComplet()) ?></h2>
        <p class="cf-text-muted cf-mb-0"><?= e($user->getEmail()) ?></p>
        <div class="cf-row cf-row--center">
            <span class="cf-badge <?= e(role_badge_class($user->getRoleNom())) ?>"><?= e($user->getRoleLibelle()) ?></span>
            <span class="cf-status cf-status--<?= e($user->getStatut()) ?>"><?= $user->isActif() ? 'Actif' : 'Inactif' ?></span>
        </div>
    </section>

    <section class="cf-card">
        <h2 class="cf-card__title">Informations du compte</h2>
        <dl class="cf-dl">
            <div><dt>Nom</dt><dd><?= e($user->getNom()) ?></dd></div>
            <div><dt>Prénom</dt><dd><?= e($user->getPrenom()) ?></dd></div>
            <div><dt>E-mail</dt><dd><?= e($user->getEmail()) ?></dd></div>
            <div><dt>Téléphone</dt><dd><?= e($user->getTelephone() ?? '—') ?></dd></div>
            <div><dt>Rôle</dt><dd><?= e($user->getRoleLibelle()) ?></dd></div>
            <div><dt>Statut</dt><dd><?= $user->isActif() ? 'Actif' : 'Inactif' ?></dd></div>
            <div><dt>Créé le</dt><dd><?= e(fr_date($user->getCreatedAt(), true)) ?></dd></div>
            <div><dt>Modifié le</dt><dd><?= e(fr_date($user->getUpdatedAt(), true)) ?></dd></div>
        </dl>

        <?php if (($can('users.toggle') || $can('users.delete')) && $canTouch && !$isSelf): ?>
            <div class="cf-detail-actions">
                <?php if ($can('users.toggle')): ?>
                    <form method="post" action="<?= e(url('user_toggle')) ?>" class="cf-inline-form" data-confirm="<?= $user->isActif() ? 'Désactiver' : 'Activer' ?> ce compte ?">
                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= (int) $user->getId() ?>">
                        <input type="hidden" name="back" value="show">
                        <button type="submit" class="cf-btn cf-btn--outline" <?= $isLastAdmin ? 'disabled' : '' ?>>
                            <?= icon($user->isActif() ? 'ban' : 'check-circle', 'cf-icon cf-icon--sm') ?> <?= $user->isActif() ? 'Désactiver le compte' : 'Activer le compte' ?>
                        </button>
                    </form>
                <?php endif; ?>
                <?php if ($can('users.delete')): ?>
                    <form method="post" action="<?= e(url('user_delete')) ?>" class="cf-inline-form" data-confirm="Supprimer définitivement ce compte ? Cette action est irréversible.">
                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= (int) $user->getId() ?>">
                        <input type="hidden" name="back" value="show">
                        <button type="submit" class="cf-btn cf-btn--danger-outline" <?= $isLastAdmin ? 'disabled' : '' ?>>
                            <?= icon('trash', 'cf-icon cf-icon--sm') ?> Supprimer
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php elseif ($isSelf): ?>
            <p class="cf-text-sm cf-text-muted cf-mb-0">C'est votre compte : utilisez « Mon profil » pour modifier vos informations.</p>
        <?php endif; ?>
    </section>
</div>
