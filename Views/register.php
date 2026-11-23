<?php
/**
 * Views/register.php — formulaire d'inscription (rôle citoyen imposé par le serveur).
 * Variables : $errors, $old, $csrfToken.
 */
?>
<section class="cf-auth">
    <div class="cf-container cf-auth__inner">
        <?php require __DIR__ . '/includes/auth_aside.php'; ?>

        <div class="cf-auth__card">
            <div class="cf-auth__tabs" role="navigation" aria-label="Connexion ou inscription">
                <a class="cf-auth__tab" href="<?= e(url('login')) ?>">Connexion</a>
                <a class="cf-auth__tab is-active" href="<?= e(url('register')) ?>" aria-current="page">Inscription</a>
            </div>

            <div class="cf-stack">
                <h1 class="cf-auth__title">Créer un compte citoyen</h1>
                <p class="cf-text-muted cf-mb-0">Les champs marqués <span class="cf-required">*</span> sont obligatoires.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="cf-alert cf-alert--error" role="alert">
                    <?= isset($errors['global']) ? e($errors['global']) : 'Veuillez corriger les erreurs indiquées ci-dessous.' ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e(url('register')) ?>" class="cf-form" data-validate novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <div class="cf-form-row">
                    <div class="cf-field">
                        <label class="cf-label" for="nom">Nom <span class="cf-required">*</span></label>
                        <input class="cf-input<?= invalid_class($errors, 'nom') ?>" type="text" id="nom" name="nom"
                               required minlength="2" maxlength="50" data-rule="name" autocomplete="family-name"
                               value="<?= e($old['nom'] ?? '') ?>" <?= aria_error($errors, 'nom', 'nom') ?>>
                        <?= field_error($errors, 'nom', 'nom') ?>
                    </div>
                    <div class="cf-field">
                        <label class="cf-label" for="prenom">Prénom <span class="cf-required">*</span></label>
                        <input class="cf-input<?= invalid_class($errors, 'prenom') ?>" type="text" id="prenom" name="prenom"
                               required minlength="2" maxlength="50" data-rule="name" autocomplete="given-name"
                               value="<?= e($old['prenom'] ?? '') ?>" <?= aria_error($errors, 'prenom', 'prenom') ?>>
                        <?= field_error($errors, 'prenom', 'prenom') ?>
                    </div>
                </div>

                <div class="cf-field">
                    <label class="cf-label" for="email">Adresse e-mail <span class="cf-required">*</span></label>
                    <input class="cf-input<?= invalid_class($errors, 'email') ?>" type="email" id="email" name="email"
                           required maxlength="150" autocomplete="email"
                           value="<?= e($old['email'] ?? '') ?>" <?= aria_error($errors, 'email', 'email') ?>>
                    <?= field_error($errors, 'email', 'email') ?>
                </div>

                <div class="cf-field">
                    <label class="cf-label" for="telephone">Téléphone <span class="cf-label__optional">(facultatif)</span></label>
                    <input class="cf-input<?= invalid_class($errors, 'telephone') ?>" type="tel" id="telephone" name="telephone"
                           maxlength="20" data-rule="phone" autocomplete="tel" placeholder="Ex. 20123456"
                           value="<?= e($old['telephone'] ?? '') ?>" <?= aria_error($errors, 'telephone', 'telephone') ?>>
                    <?= field_error($errors, 'telephone', 'telephone') ?>
                </div>

                <div class="cf-field">
                    <label class="cf-label" for="password">Mot de passe <span class="cf-required">*</span></label>
                    <input class="cf-input<?= invalid_class($errors, 'password') ?>" type="password" id="password" name="password"
                           required data-rule="password" data-strength="#password-strength" autocomplete="new-password"
                           <?= aria_error($errors, 'password', 'password') ?>>
                    <div class="cf-strength" id="password-strength" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
                    <span class="cf-field-hint">8 caractères min., une majuscule, une minuscule, un chiffre.</span>
                    <?= field_error($errors, 'password', 'password') ?>
                </div>

                <div class="cf-field">
                    <label class="cf-label" for="password_confirm">Confirmer le mot de passe <span class="cf-required">*</span></label>
                    <input class="cf-input<?= invalid_class($errors, 'password_confirm') ?>" type="password" id="password_confirm"
                           name="password_confirm" required data-match="#password" autocomplete="new-password"
                           <?= aria_error($errors, 'password_confirm', 'password_confirm') ?>>
                    <?= field_error($errors, 'password_confirm', 'password_confirm') ?>
                </div>

                <button type="submit" class="cf-btn cf-btn--primary cf-btn--lg cf-btn--block">Créer mon compte</button>
            </form>
        </div>
    </div>
</section>
