<?php
/**
 * Views/includes/footer.php — pied de page du Front Office + bouton « Retour en haut »
 * (atelier, étape 6 : le bouton est inclus dans le même fichier que le footer).
 */
?>
<footer class="cf-footer">
    <div class="cf-container">
        <div class="cf-footer__top">
            <div class="cf-footer__brand">
                <a class="cf-logo cf-logo--light" href="<?= e(url('home')) ?>">
                    <span class="cf-logo__mark"><?= icon('logo') ?></span>
                    <span>City<span class="cf-logo__accent">Fix</span></span>
                </a>
                <p>Plateforme intelligente de signalement citoyen pour une gestion urbaine plus efficace.</p>
            </div>
            <nav class="cf-footer__cols" aria-label="Liens du pied de page">
                <div>
                    <h2 class="cf-footer__title">Plateforme</h2>
                    <ul>
                        <li><a href="<?= e(url('home')) ?>">Accueil</a></li>
                        <li><a href="<?= e(url('home')) ?>#services">Services</a></li>
                        <li><a href="<?= e(url('about')) ?>">À propos</a></li>
                    </ul>
                </div>
                <div>
                    <h2 class="cf-footer__title">Compte</h2>
                    <ul>
                        <?php if ($authUser !== null): ?>
                            <li><a href="<?= e(url('profile')) ?>">Mon espace</a></li>
                        <?php else: ?>
                            <li><a href="<?= e(url('login')) ?>">Connexion</a></li>
                            <li><a href="<?= e(url('register')) ?>">Inscription</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
        <p class="cf-footer__copy">© <?= date('Y') ?> CITYFIX — Projet Technologies Web 2A, ESPRIT</p>
    </div>
</footer>

<a href="#top" class="cf-back-to-top" aria-label="Retour en haut de la page" data-back-to-top><?= icon('arrow-up') ?></a>
