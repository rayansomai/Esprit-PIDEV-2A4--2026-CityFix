<?php
/**
 * Views/reset_password.php — choix d'un nouveau mot de passe à partir du lien reçu.
 * Variables : $token, $validToken, $errors, $csrfToken.
 */
?>
<section class="cf-auth cf-auth--single">
    <div class="cf-container">
        <div class="cf-auth__card cf-auth__card--narrow">
            <?php if (!$validToken): ?>
                <span class="cf-icon-box cf-icon-box--lg cf-icon-box--red"><?= icon('alert', 'cf-icon cf-icon--lg') ?></span>
                <h1 class="cf-auth__title">Lien invalide ou expiré</h1>
                <p class="cf-text-muted">Ce lien de réinitialisation n'est plus valable (il a expiré ou a déjà été utilisé).</p>
                <a class="cf-btn cf-btn--primary cf-btn--block" href="<?= e(url('forgot_password')) ?>">Faire une nouvelle demande</a>
            <?php else: ?>
                <div class="cf-stack">
                    <h1 class="cf-auth__title">Nouveau mot de passe</h1>
                    <p class="cf-text-muted cf-mb-0">Choisissez un mot de passe que vous n'utilisez pas ailleurs.</p>
                </div>

                <?php if (isset($errors['global'])): ?>
                    <div class="cf-alert cf-alert--error" role="alert"><?= e($errors['global']) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= e(url('reset_password')) ?>" class="cf-form" data-validate novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="token" value="<?= e($token) ?>">

                    <div class="cf-field">
                        <label class="cf-label" for="password">Nouveau mot de passe</label>
                        <input class="cf-input<?= invalid_class($errors, 'password') ?>" type="password" id="password" name="password"
                               required data-rule="password" data-strength="#password-strength" autocomplete="new-password"
                               <?= aria_error($errors, 'password', 'password') ?>>
                        <div class="cf-strength" id="password-strength" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
                        <span class="cf-field-hint">8 caractères min., une majuscule, une minuscule, un chiffre.</span>
                        <?= field_error($errors, 'password', 'password') ?>
                    </div>

                    <div class="cf-field">
                        <label class="cf-label" for="password_confirm">Confirmer</label>
                        <input class="cf-input<?= invalid_class($errors, 'password_confirm') ?>" type="password" id="password_confirm"
                               name="password_confirm" required data-match="#password" autocomplete="new-password"
                               <?= aria_error($errors, 'password_confirm', 'password_confirm') ?>>
                        <?= field_error($errors, 'password_confirm', 'password_confirm') ?>
                    </div>

                    <button type="submit" class="cf-btn cf-btn--primary cf-btn--lg cf-btn--block">Enregistrer le mot de passe</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
