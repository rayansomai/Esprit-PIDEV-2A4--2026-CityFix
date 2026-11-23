<?php
/**
 * Views/admin/users/create.php — création d'un utilisateur (Back Office).
 */
$mode = 'create';
?>
<div class="cf-page-head">
    <div>
        <nav class="cf-breadcrumb" aria-label="Fil d'Ariane"><a href="<?= e(url('dashboard')) ?>">Back Office</a> › <a href="<?= e(url('users')) ?>">Utilisateurs</a> › <span aria-current="page">Nouvel utilisateur</span></nav>
        <h1>Nouvel utilisateur</h1>
        <p class="cf-text-muted cf-mb-0">Créez un compte et attribuez-lui un rôle.</p>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="cf-alert cf-alert--error cf-mb-4" role="alert">Veuillez corriger les erreurs indiquées dans le formulaire.</div>
<?php endif; ?>

<form method="post" action="<?= e(url('user_store')) ?>" class="cf-card cf-form cf-form--admin" data-validate novalidate>
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
    <?php require __DIR__ . '/_form.php'; ?>
    <div class="cf-form-actions">
        <a class="cf-btn cf-btn--outline" href="<?= e(url('users')) ?>">Annuler</a>
        <button type="submit" class="cf-btn cf-btn--primary"><?= icon('plus', 'cf-icon cf-icon--sm') ?> Créer l'utilisateur</button>
    </div>
</form>
