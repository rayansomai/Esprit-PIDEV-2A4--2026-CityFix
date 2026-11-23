<?php
/**
 * Views/admin/users/_form.php — champs communs aux formulaires de création et de modification.
 * Variables : $old, $errors, $roles, $mode ('create' | 'edit'), $lockAccess (bool, édition).
 */
$lockAccess = $lockAccess ?? false;
$isAdmin = $authUser->getRoleNom() === 'admin';
$roleDescriptions = [
    'citoyen' => 'Espace citoyen uniquement.',
    'agent'   => 'Back Office selon les permissions du rôle.',
    'admin'   => 'Accès complet et gestion des comptes.',
];
?>
<fieldset class="cf-fieldset">
    <legend>Identité</legend>
    <div class="cf-form-row">
        <div class="cf-field">
            <label class="cf-label" for="nom">Nom <span class="cf-required">*</span></label>
            <input class="cf-input<?= invalid_class($errors, 'nom') ?>" type="text" id="nom" name="nom" required minlength="2" maxlength="50"
                   data-rule="name" value="<?= e($old['nom'] ?? '') ?>" <?= aria_error($errors, 'nom', 'nom') ?>>
            <?= field_error($errors, 'nom', 'nom') ?>
        </div>
        <div class="cf-field">
            <label class="cf-label" for="prenom">Prénom <span class="cf-required">*</span></label>
            <input class="cf-input<?= invalid_class($errors, 'prenom') ?>" type="text" id="prenom" name="prenom" required minlength="2" maxlength="50"
                   data-rule="name" value="<?= e($old['prenom'] ?? '') ?>" <?= aria_error($errors, 'prenom', 'prenom') ?>>
            <?= field_error($errors, 'prenom', 'prenom') ?>
        </div>
        <div class="cf-field">
            <label class="cf-label" for="email">E-mail <span class="cf-required">*</span></label>
            <input class="cf-input<?= invalid_class($errors, 'email') ?>" type="email" id="email" name="email" required maxlength="150"
                   value="<?= e($old['email'] ?? '') ?>" <?= aria_error($errors, 'email', 'email') ?>>
            <?= field_error($errors, 'email', 'email') ?>
        </div>
        <div class="cf-field">
            <label class="cf-label" for="telephone">Téléphone <span class="cf-label__optional">(facultatif)</span></label>
            <input class="cf-input<?= invalid_class($errors, 'telephone') ?>" type="tel" id="telephone" name="telephone" maxlength="20"
                   data-rule="phone" value="<?= e($old['telephone'] ?? '') ?>" <?= aria_error($errors, 'telephone', 'telephone') ?>>
            <?= field_error($errors, 'telephone', 'telephone') ?>
        </div>
    </div>
</fieldset>

<fieldset class="cf-fieldset">
    <legend>Rôle <span class="cf-required">*</span></legend>
    <?php if ($lockAccess): ?>
        <input type="hidden" name="role_id" value="<?= (int) $old['role_id'] ?>">
    <?php endif; ?>
    <div class="cf-choices">
        <?php foreach ($roles as $role): ?>
            <?php
            $checked  = (int) ($old['role_id'] ?? 0) === $role->getId();
            $disabled = $lockAccess || ($role->getNom() === 'admin' && !$isAdmin);
            ?>
            <label class="cf-choice">
                <input type="radio" name="role_id" value="<?= (int) $role->getId() ?>" <?= $checked ? 'checked' : '' ?> <?= $disabled ? 'disabled' : '' ?> required>
                <span>
                    <strong><?= e($role->getLibelle()) ?></strong>
                    <span class="cf-text-xs cf-text-muted"><?= e($roleDescriptions[$role->getNom()] ?? $role->getDescription()) ?></span>
                </span>
            </label>
        <?php endforeach; ?>
    </div>
    <?= field_error($errors, 'role_id', 'role_id') ?>
</fieldset>

<fieldset class="cf-fieldset">
    <legend>Statut et mot de passe</legend>
    <?php if ($lockAccess): ?>
        <input type="hidden" name="statut" value="<?= e($old['statut']) ?>">
    <?php endif; ?>
    <div class="cf-field">
        <label class="cf-label" for="statut">Statut du compte</label>
        <select class="cf-select<?= invalid_class($errors, 'statut') ?>" id="statut" name="statut" <?= $lockAccess ? 'disabled' : '' ?>>
            <option value="actif" <?= ($old['statut'] ?? 'actif') === 'actif' ? 'selected' : '' ?>>Actif — l'utilisateur peut se connecter</option>
            <option value="inactif" <?= ($old['statut'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif — connexion refusée</option>
        </select>
        <?= field_error($errors, 'statut', 'statut') ?>
    </div>

    <div class="cf-form-row">
        <div class="cf-field">
            <label class="cf-label" for="password">
                <?= $mode === 'create' ? 'Mot de passe <span class="cf-required">*</span>' : 'Nouveau mot de passe <span class="cf-label__optional">(laisser vide pour ne pas changer)</span>' ?>
            </label>
            <input class="cf-input<?= invalid_class($errors, 'password') ?>" type="password" id="password" name="password" autocomplete="new-password"
                   data-rule="password" <?= $mode === 'create' ? 'required' : '' ?> <?= aria_error($errors, 'password', 'password') ?>>
            <span class="cf-field-hint">8 caractères min., une majuscule, une minuscule, un chiffre.</span>
            <?= field_error($errors, 'password', 'password') ?>
        </div>
        <div class="cf-field">
            <label class="cf-label" for="password_confirm">Confirmer le mot de passe</label>
            <input class="cf-input<?= invalid_class($errors, 'password_confirm') ?>" type="password" id="password_confirm" name="password_confirm"
                   autocomplete="new-password" data-match="#password" <?= $mode === 'create' ? 'required' : '' ?> <?= aria_error($errors, 'password_confirm', 'password_confirm') ?>>
            <?= field_error($errors, 'password_confirm', 'password_confirm') ?>
        </div>
    </div>
</fieldset>
