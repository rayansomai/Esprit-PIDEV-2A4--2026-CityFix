<?php
/**
 * config/app.php
 * Constantes générales de l'application et petites fonctions d'aide pour l'affichage.
 */

declare(strict_types=1);

// ---------------------------------------------------------------------
// Application
// ---------------------------------------------------------------------
const APP_NAME = 'CITYFIX';
const APP_ENV  = 'local';          // 'local' (développement) ou 'production'

// ---------------------------------------------------------------------
// Sessions (workshop, Partie 5 : expiration par inactivité)
//   Normal : 30 minutes. Pour tester l'expiration, passer SESSION_TEST_MODE à true
//   (10 secondes), puis remettre à false après le test.
// ---------------------------------------------------------------------
const SESSION_TEST_MODE = false;
const SESSION_LIFETIME  = SESSION_TEST_MODE ? 10 : 1800;

// ---------------------------------------------------------------------
// Réinitialisation du mot de passe
//   MAIL_MODE = 'log'  : mode test local -> aucun e-mail réel ; le lien est écrit dans
//                        storage/logs/mail.log et affiché à l'écran (APP_ENV = 'local').
//   MAIL_MODE = 'smtp' : envoi réel avec PHPMailer (paramètres dans config/mail.local.php).
// ---------------------------------------------------------------------
const MAIL_MODE            = 'log';
const RESET_TOKEN_LIFETIME = 1800;  // 30 minutes
const MAIL_LOG_FILE        = __DIR__ . '/../storage/logs/mail.log';

// ---------------------------------------------------------------------
// Pagination du Back Office
// ---------------------------------------------------------------------
const USERS_PER_PAGE = 10;

// ---------------------------------------------------------------------
// Fonctions d'aide pour les vues
// ---------------------------------------------------------------------

/** Échappe une valeur avant affichage HTML (protection XSS, workshop Partie 7.3). */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Construit une URL interne : url('login') -> index.php?action=login */
function url(string $action, array $params = []): string
{
    $query = array_merge(['action' => $action], $params);
    return 'index.php?' . http_build_query($query);
}

/** Chemin d'une ressource publique (CSS, JS, images). */
function asset(string $path): string
{
    return 'public/assets/' . ltrim($path, '/');
}
