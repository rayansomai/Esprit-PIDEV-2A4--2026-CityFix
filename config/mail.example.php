<?php
/**
 * config/mail.example.php — MODÈLE de configuration SMTP (PHPMailer).
 *
 * 1. Copier ce fichier sous le nom config/mail.local.php
 * 2. Remplir vos paramètres (ex. Gmail : smtp.gmail.com, port 587, « mot de passe d'application »)
 * 3. Dans config/app.php, passer MAIL_MODE à 'smtp'
 *
 * config/mail.local.php est exclu de Git (.gitignore) : vos identifiants ne sont jamais publiés.
 */

return [
    'host'       => 'smtp.gmail.com',
    'port'       => 587,
    'encryption' => 'tls',                 // 'tls' (port 587) ou 'ssl' (port 465)
    'username'   => 'votre.adresse@gmail.com',
    'password'   => 'mot-de-passe-application',
    'from_email' => 'votre.adresse@gmail.com',
    'from_name'  => 'CITYFIX',
];
