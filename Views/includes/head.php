<?php
/**
 * Views/includes/head.php — contenu de <head> du Front Office (atelier, étape 3).
 * Variable attendue : $title (définie par le contrôleur).
 */
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="CITYFIX — plateforme intelligente de signalement citoyen pour améliorer la gestion des problèmes urbains.">
<title><?= e($title ?? APP_NAME) ?></title>
<link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('css/base.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/components.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/front.css')) ?>">
