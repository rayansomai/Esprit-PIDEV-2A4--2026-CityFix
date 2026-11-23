<?php
/**
 * Views/index.php — page d'accueil publique (Front Office). Affichage uniquement.
 */
?>
<!-- HERO -->
<section class="cf-hero">
    <div class="cf-container cf-hero__inner">
        <div class="cf-hero__text">
            <span class="cf-hero__tag">Plateforme de signalement citoyen</span>
            <h1 class="cf-hero__title">Signalez. Suivez.<br><span>Améliorez votre ville.</span></h1>
            <p class="cf-hero__lead">Nid-de-poule, lampadaire en panne, déchets, fuite d'eau : CITYFIX relie les citoyens aux services municipaux pour que chaque problème urbain soit pris en charge.</p>
            <div class="cf-hero__actions">
                <?php if ($authUser === null): ?>
                    <a class="cf-btn cf-btn--light cf-btn--lg" href="<?= e(url('register')) ?>">Commencer gratuitement</a>
                <?php else: ?>
                    <a class="cf-btn cf-btn--light cf-btn--lg" href="<?= e(url('profile')) ?>">Accéder à mon espace</a>
                <?php endif; ?>
                <a class="cf-btn cf-btn--on-dark cf-btn--lg" href="#concept">Découvrir le concept</a>
            </div>
        </div>

        <div class="cf-hero__visual" aria-hidden="true">
            <span class="cf-map__road cf-map__road--h"></span>
            <span class="cf-map__road cf-map__road--v"></span>
            <span class="cf-map__pin cf-map__pin--amber cf-map__pin--p1"></span>
            <span class="cf-map__pin cf-map__pin--green cf-map__pin--p2"></span>
            <span class="cf-map__pin cf-map__pin--blue cf-map__pin--p3"></span>
            <span class="cf-map__pin cf-map__pin--green cf-map__pin--p4"></span>
            <div class="cf-map__card">
                <span class="cf-icon-box cf-icon-box--green"><?= icon('bulb') ?></span>
                <div class="cf-map__card-text"><strong>Éclairage public en panne</strong><span>Exemple de signalement pris en charge</span></div>
                <span class="cf-badge cf-badge--citoyen">En cours</span>
            </div>
        </div>
    </div>
</section>

<!-- CONCEPT SMART CITY -->
<section class="cf-section cf-section--white" id="concept">
    <div class="cf-container">
        <div class="cf-section__head">
            <span class="cf-eyebrow">Le concept</span>
            <h2>Une ville plus intelligente, construite avec ses habitants</h2>
            <p class="cf-text-muted">La Smart City utilise le numérique pour mieux gérer l'espace urbain. CITYFIX met cette idée au service du quotidien : chaque citoyen devient un capteur, chaque agent dispose d'informations précises.</p>
        </div>
        <div class="cf-grid">
            <article class="cf-feature cf-card--hover">
                <span class="cf-icon-box cf-icon-box--lg cf-icon-box--dark"><?= icon('users', 'cf-icon cf-icon--lg') ?></span>
                <h3>Participation citoyenne</h3>
                <p>Les habitants signalent ce qu'ils voient, là où ils le voient.</p>
            </article>
            <article class="cf-feature cf-card--hover">
                <span class="cf-icon-box cf-icon-box--lg cf-icon-box--dark"><?= icon('chart', 'cf-icon cf-icon--lg') ?></span>
                <h3>Décisions éclairées</h3>
                <p>Les services municipaux priorisent grâce à des données centralisées.</p>
            </article>
            <article class="cf-feature cf-card--hover">
                <span class="cf-icon-box cf-icon-box--lg cf-icon-box--dark"><?= icon('shield-check', 'cf-icon cf-icon--lg') ?></span>
                <h3>Transparence</h3>
                <p>Chacun suit l'avancement de ses demandes, étape par étape.</p>
            </article>
        </div>
    </div>
</section>

<!-- SERVICES -->
<section class="cf-section" id="services">
    <div class="cf-container">
        <div class="cf-section__head">
            <span class="cf-eyebrow">Nos services</span>
            <h2>Tout ce dont un citoyen a besoin, au même endroit</h2>
        </div>
        <div class="cf-grid">
            <article class="cf-card cf-card--hover cf-service">
                <span class="cf-icon-box cf-icon-box--lg cf-icon-box--green"><?= icon('pin', 'cf-icon cf-icon--lg') ?></span>
                <h3>Signalements</h3>
                <p>Photo, catégorie et position sur la carte en quelques secondes.</p>
                <span class="cf-badge cf-badge--neutral">Bientôt disponible</span>
            </article>
            <article class="cf-card cf-card--hover cf-service">
                <span class="cf-icon-box cf-icon-box--lg cf-icon-box--blue"><?= icon('wrench', 'cf-icon cf-icon--lg') ?></span>
                <h3>Interventions</h3>
                <p>Les services municipaux planifient et traitent les demandes.</p>
                <span class="cf-badge cf-badge--neutral">Bientôt disponible</span>
            </article>
            <article class="cf-card cf-card--hover cf-service">
                <span class="cf-icon-box cf-icon-box--lg cf-icon-box--amber"><?= icon('file', 'cf-icon cf-icon--lg') ?></span>
                <h3>Documents administratifs</h3>
                <p>Demandez un document en ligne et prenez rendez-vous pour le retrait.</p>
                <span class="cf-badge cf-badge--neutral">Bientôt disponible</span>
            </article>
            <article class="cf-card cf-card--hover cf-service">
                <span class="cf-icon-box cf-icon-box--lg cf-icon-box--navy"><?= icon('user', 'cf-icon cf-icon--lg') ?></span>
                <h3>Espace citoyen</h3>
                <p>Un compte sécurisé pour gérer votre profil et vos démarches.</p>
                <span class="cf-badge cf-badge--citoyen">Disponible</span>
            </article>
        </div>
    </div>
</section>

<!-- ÉTAPES -->
<section class="cf-section cf-section--white">
    <div class="cf-container">
        <h2 class="cf-text-center">Comment ça marche ?</h2>
        <ol class="cf-steps">
            <li class="cf-step"><span class="cf-step__num">1</span><h3>Créez votre compte</h3><p>Inscription rapide et sécurisée.</p></li>
            <li class="cf-step"><span class="cf-step__num">2</span><h3>Signalez un problème</h3><p>Décrivez, ajoutez une photo, localisez.</p></li>
            <li class="cf-step"><span class="cf-step__num cf-step__num--green">3</span><h3>Suivez la résolution</h3><p>Consultez l'état d'avancement de vos demandes.</p></li>
        </ol>
    </div>
</section>

<!-- APPEL À L'ACTION -->
<?php if ($authUser === null): ?>
<section class="cf-section cf-section--white cf-cta-wrap">
    <div class="cf-container">
        <div class="cf-cta">
            <div>
                <h2>Votre ville mérite votre voix.</h2>
                <p>Rejoignez CITYFIX et participez à l'amélioration de votre quartier.</p>
            </div>
            <a class="cf-btn cf-btn--light cf-btn--lg" href="<?= e(url('register')) ?>">Créer mon compte</a>
        </div>
    </div>
</section>
<?php endif; ?>
