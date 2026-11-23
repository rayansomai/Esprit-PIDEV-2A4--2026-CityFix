<?php
/**
 * Views/admin/users/edit.php — modification d'un utilisateur (Back Office).
 * Variables : $user, $old, $errors, $roles, $lockAccess, $isLastAdmin.
 */
$mode = 'edit';
$isSelf = $user->getId() === $authUser->getId();
?>
<div class="cf-page-head">
    <div>
        <nav class="cf-breadcrumb" aria-label="Fil d'Ariane"><a href="<?= e(url('dashboard')) ?>">Back Office</a> › <a href="<?= e(url('users')) ?>">Utilisateurs</a> › <span aria-current="page">Modifier</span></nav>
        <h1>Modifier l'utilisateur</h1>
        <p class="cf-text-muted cf-mb-0"><?= e($user->getNomComplet()) ?> — <?= e($user->getEmail()) ?></p>
    </div>
</div>

<?php if ($isLastAdmin): ?>
    <div class="cf-alert cf-alert--warning cf-mb-4" role="status"><strong>Dernier administrateur actif.</strong> Son rôle et son statut sont verrouillés pour éviter de perdre l'accès au Back Office.</div>
<?php elseif ($isSelf): ?>
    <div class="cf-alert cf-alert--info cf-mb-4" role="status">C'est votre propre compte : votre rôle et votre statut ne peuvent pas être modifiés ici.</div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="cf-alert cf-alert--error cf-mb-4" role="alert">Veuillez corriger les erreurs indiquées dans le formulaire.</div>
<?php endif; ?>

<form method="post" action="<?= e(url('user_update')) ?>" class="cf-card cf-form cf-form--admin" data-validate novalidate>
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
    <input type="hidden" name="id" value="<?= (int) $user->getId() ?>">
    <?php require __DIR__ . '/_form.php'; ?>
    <div class="cf-form-actions">
        <a class="cf-btn cf-btn--outline" href="<?= e(url('user_show', ['id' => $user->getId()])) ?>">Annuler</a>
        <button type="submit" class="cf-btn cf-btn--primary">Enregistrer les modifications</button>
    </div>
</form>
