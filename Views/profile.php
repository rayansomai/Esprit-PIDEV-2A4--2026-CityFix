<?php
/**
 * Views/profile.php — espace citoyen : informations personnelles et mot de passe.
 * Variables : $user (UserModel), $errors, $old, $csrfToken.
 */
$values = $old ?: [
    'nom' => $user->getNom(), 'prenom' => $user->getPrenom(),
    'email' => $user->getEmail(), 'telephone' => (string) $user->getTelephone(),
];
$roleBadge = ['citoyen' => 'cf-badge--citoyen', 'agent' => 'cf-badge--agent', 'admin' => 'cf-badge--admin'][$user->getRoleNom()] ?? 'cf-badge--neutral';
?>
<section class="cf-section cf-section--tight">
    <div class="cf-container cf-stack cf-profile">
        <!-- Bandeau -->
        <div class="cf-profile__banner">
            <span class="cf-avatar cf-avatar--lg cf-profile__avatar" aria-hidden="true"><?= e($user->getInitiales()) ?></span>
            <div class="cf-profile__identity">
                <h1><?= e($user->getNomComplet()) ?></h1>
                <div class="cf-row">
                    <span class="cf-badge <?= e($roleBadge) ?>"><?= e($user->getRoleLibelle()) ?></span>
                    <span class="cf-badge cf-badge--on-dark">Compte actif</span>
                    <span class="cf-profile__since">Membre depuis le <?= e(date('d/m/Y', strtotime($user->getCreatedAt()))) ?></span>
                </div>
            </div>
        </div>

        <div class="cf-profile__grid">
            <!-- Informations personnelles -->
            <form method="post" action="<?= e(url('profile_update')) ?>" class="cf-card cf-form" data-validate novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <div>
                    <h2 class="cf-card__title">Informations personnelles</h2>
                    <p class="cf-text-sm cf-text-muted cf-mb-0">Mettez à jour vos coordonnées.</p>
                </div>

                <div class="cf-form-row">
                    <div class="cf-field">
                        <label class="cf-label" for="nom">Nom</label>
                        <input class="cf-input<?= invalid_class($errors, 'nom') ?>" type="text" id="nom" name="nom" required minlength="2" maxlength="50"
                               data-rule="name" autocomplete="family-name" value="<?= e($values['nom']) ?>" <?= aria_error($errors, 'nom', 'nom') ?>>
                        <?= field_error($errors, 'nom', 'nom') ?>
                    </div>
                    <div class="cf-field">
                        <label class="cf-label" for="prenom">Prénom</label>
                        <input class="cf-input<?= invalid_class($errors, 'prenom') ?>" type="text" id="prenom" name="prenom" required minlength="2" maxlength="50"
                               data-rule="name" autocomplete="given-name" value="<?= e($values['prenom']) ?>" <?= aria_error($errors, 'prenom', 'prenom') ?>>
                        <?= field_error($errors, 'prenom', 'prenom') ?>
                    </div>
                    <div class="cf-field">
                        <label class="cf-label" for="email">E-mail</label>
                        <input class="cf-input<?= invalid_class($errors, 'email') ?>" type="email" id="email" name="email" required maxlength="150"
                               autocomplete="email" value="<?= e($values['email']) ?>" <?= aria_error($errors, 'email', 'email') ?>>
                        <?= field_error($errors, 'email', 'email') ?>
                    </div>
                    <div class="cf-field">
                        <label class="cf-label" for="telephone">Téléphone <span class="cf-label__optional">(facultatif)</span></label>
                        <input class="cf-input<?= invalid_class($errors, 'telephone') ?>" type="tel" id="telephone" name="telephone" maxlength="20"
                               data-rule="phone" autocomplete="tel" placeholder="Ex. 20123456" value="<?= e($values['telephone']) ?>" <?= aria_error($errors, 'telephone', 'telephone') ?>>
                        <?= field_error($errors, 'telephone', 'telephone') ?>
                    </div>
                </div>

                <div class="cf-form-actions">
                    <a class="cf-btn cf-btn--outline" href="<?= e(url('profile')) ?>">Annuler</a>
                    <button type="submit" class="cf-btn cf-btn--primary">Enregistrer</button>
                </div>
            </form>

            <div class="cf-stack">
                <!-- Mot de passe -->
                <form method="post" action="<?= e(url('password_update')) ?>" class="cf-card cf-form" data-validate novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <div class="cf-row">
                        <span class="cf-icon-box cf-icon-box--navy"><?= icon('key') ?></span>
                        <h2 class="cf-card__title">Changer le mot de passe</h2>
                    </div>
                    <div class="cf-field">
                        <label class="cf-label" for="current_password">Mot de passe actuel</label>
                        <input class="cf-input<?= invalid_class($errors, 'current_password') ?>" type="password" id="current_password" name="current_password"
                               required autocomplete="current-password" <?= aria_error($errors, 'current_password', 'current_password') ?>>
                        <?= field_error($errors, 'current_password', 'current_password') ?>
                    </div>
                    <div class="cf-field">
                        <label class="cf-label" for="new_password">Nouveau mot de passe</label>
                        <input class="cf-input<?= invalid_class($errors, 'new_password') ?>" type="password" id="new_password" name="new_password"
                               required data-rule="password" data-strength="#new-password-strength" autocomplete="new-password"
                               <?= aria_error($errors, 'new_password', 'new_password') ?>>
                        <div class="cf-strength" id="new-password-strength" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
                        <?= field_error($errors, 'new_password', 'new_password') ?>
                    </div>
                    <div class="cf-field">
                        <label class="cf-label" for="new_password_confirm">Confirmer</label>
                        <input class="cf-input<?= invalid_class($errors, 'new_password_confirm') ?>" type="password" id="new_password_confirm" name="new_password_confirm"
                               required data-match="#new_password" autocomplete="new-password" <?= aria_error($errors, 'new_password_confirm', 'new_password_confirm') ?>>
                        <?= field_error($errors, 'new_password_confirm', 'new_password_confirm') ?>
                    </div>
                    <button type="submit" class="cf-btn cf-btn--secondary">Mettre à jour</button>
                </form>

                <!-- Modules à venir -->
                <div class="cf-card cf-card--dashed">
                    <span class="cf-badge cf-badge--neutral">Bientôt disponible</span>
                    <h3>Mes signalements &amp; mes demandes</h3>
                    <p class="cf-text-sm cf-text-muted cf-mb-0">Ces espaces arriveront avec les modules 2 et 4.</p>
                </div>
            </div>
        </div>
    </div>
</section>
