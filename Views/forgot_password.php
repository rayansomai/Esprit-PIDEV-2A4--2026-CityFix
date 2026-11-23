<?php
/**
 * Views/forgot_password.php — demande de lien de réinitialisation.
 * Variables : $error, $sent, $testLink (mode test local uniquement), $csrfToken.
 */
?>
<section class="cf-auth cf-auth--single">
    <div class="cf-container">
        <div class="cf-auth__card cf-auth__card--narrow">
            <?php if (!$sent): ?>
                <span class="cf-icon-box cf-icon-box--lg cf-icon-box--navy"><?= icon('key', 'cf-icon cf-icon--lg') ?></span>
                <div class="cf-stack">
                    <h1 class="cf-auth__title">Mot de passe oublié ?</h1>
                    <p class="cf-text-muted cf-mb-0">Saisissez votre e-mail. Si un compte actif existe, vous recevrez un lien valable 30 minutes.</p>
                </div>

                <?php if ($error): ?>
                    <div class="cf-alert cf-alert--error" role="alert"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= e(url('forgot_password')) ?>" class="cf-form" data-validate novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <div class="cf-field">
                        <label class="cf-label" for="email">Adresse e-mail</label>
                        <input class="cf-input" type="email" id="email" name="email" required maxlength="150" autocomplete="email" aria-describedby="email-error">
                        <span class="cf-field-error" id="email-error" hidden></span>
                    </div>
                    <button type="submit" class="cf-btn cf-btn--primary cf-btn--lg cf-btn--block">Envoyer le lien</button>
                </form>
            <?php else: ?>
                <span class="cf-icon-box cf-icon-box--lg cf-icon-box--green"><?= icon('mail', 'cf-icon cf-icon--lg') ?></span>
                <div class="cf-stack">
                    <h1 class="cf-auth__title">Vérifiez votre boîte mail</h1>
                    <p class="cf-text-muted cf-mb-0">Si un compte actif correspond à cette adresse, un lien de réinitialisation vient d'être envoyé. Il est valable 30 minutes.</p>
                </div>

                <?php if (MAIL_MODE === 'log' && APP_ENV === 'local'): ?>
                    <div class="cf-test-mode">
                        <span class="cf-test-mode__tag">Mode test local</span>
                        <p>Aucun e-mail réel n'est envoyé. Les liens sont écrits dans <code>storage/logs/mail.log</code>. Ce bloc disparaît lorsque l'envoi SMTP est activé.</p>
                        <?php if ($testLink !== null): ?>
                            <a class="cf-test-mode__link" href="<?= e($testLink) ?>">Ouvrir le lien de réinitialisation</a>
                        <?php else: ?>
                            <p class="cf-mb-0"><em>Aucun compte actif pour cette adresse : aucun lien généré.</em></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <a class="cf-text-sm cf-text-center" href="<?= e(url('login')) ?>">← Retour à la connexion</a>
        </div>
    </div>
</section>
