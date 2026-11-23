<?php
/**
 * Views/login.php — formulaire de connexion. Variables : $error, $oldEmail, $csrfToken.
 */
?>
<section class="cf-auth">
    <div class="cf-container cf-auth__inner">
        <?php require __DIR__ . '/includes/auth_aside.php'; ?>

        <div class="cf-auth__card">
            <div class="cf-auth__tabs" role="navigation" aria-label="Connexion ou inscription">
                <a class="cf-auth__tab is-active" href="<?= e(url('login')) ?>" aria-current="page">Connexion</a>
                <a class="cf-auth__tab" href="<?= e(url('register')) ?>">Inscription</a>
            </div>

            <div class="cf-stack">
                <h1 class="cf-auth__title">Bon retour</h1>
                <p class="cf-text-muted cf-mb-0">Connectez-vous à votre espace CITYFIX.</p>
            </div>

            <?php if ($error): ?>
                <div class="cf-alert cf-alert--error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= e(url('login')) ?>" class="cf-form" data-validate novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <div class="cf-field">
                    <label class="cf-label" for="email">Adresse e-mail</label>
                    <input class="cf-input" type="email" id="email" name="email" required maxlength="150"
                           autocomplete="email" value="<?= e($oldEmail) ?>" aria-describedby="email-error">
                    <span class="cf-field-error" id="email-error" hidden></span>
                </div>

                <div class="cf-field">
                    <div class="cf-row cf-row--between">
                        <label class="cf-label" for="password">Mot de passe</label>
                        <a class="cf-text-sm" href="<?= e(url('forgot_password')) ?>">Mot de passe oublié ?</a>
                    </div>
                    <input class="cf-input" type="password" id="password" name="password" required
                           autocomplete="current-password" aria-describedby="password-error">
                    <span class="cf-field-error" id="password-error" hidden></span>
                </div>

                <button type="submit" class="cf-btn cf-btn--primary cf-btn--lg cf-btn--block">Se connecter</button>
            </form>

            <p class="cf-text-sm cf-text-center cf-mb-0">Pas encore de compte ? <a href="<?= e(url('register')) ?>">Créer un compte</a></p>
        </div>
    </div>
</section>
