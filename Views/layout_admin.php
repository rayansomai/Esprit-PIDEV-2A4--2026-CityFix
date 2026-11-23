<?php
/**
 * Views/layout_admin.php — habillage du Back Office (atelier, Partie 2 — étape 8 ;
 * workshop, Annexe B). Reçoit $title, $currentPage et $content.
 */
require_once __DIR__ . '/includes/icons.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require __DIR__ . '/includes/admin_head.php'; ?>
</head>
<body class="cf-admin">
    <a class="cf-skip-link" href="#contenu">Aller au contenu</a>
    <div class="cf-admin-shell">
        <?php require __DIR__ . '/includes/admin_sidebar.php'; ?>

        <div class="cf-admin-main">
            <?php require __DIR__ . '/includes/admin_header.php'; ?>

            <main id="contenu" class="cf-admin-content" tabindex="-1">
                <?php require __DIR__ . '/includes/flash.php'; ?>
                <?= $content ?>
            </main>

            <?php require __DIR__ . '/includes/admin_footer.php'; ?>
        </div>
    </div>
    <?php require __DIR__ . '/includes/admin_scripts.php'; ?>
</body>
</html>
