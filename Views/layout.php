<?php
/**
 * Views/layout.php — habillage du Front Office (atelier « Intégration Template », étape 8).
 * Reçoit $title, $currentPage et $content (préparés par Controller::render())
 * et assemble les blocs communs dans l'ordre d'affichage.
 */
require_once __DIR__ . '/includes/icons.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require __DIR__ . '/includes/head.php'; ?>
</head>
<body class="cf-front" id="top">
    <a class="cf-skip-link" href="#contenu">Aller au contenu</a>
    <?php require __DIR__ . '/includes/spinner.php'; ?>
    <?php require __DIR__ . '/includes/navbar.php'; ?>

    <main id="contenu" tabindex="-1">
        <?php if (!empty($flash)): ?>
            <div class="cf-container cf-front-flash"><?php require __DIR__ . '/includes/flash.php'; ?></div>
        <?php endif; ?>
        <?= $content ?>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>
    <?php require __DIR__ . '/includes/scripts.php'; ?>
</body>
</html>
