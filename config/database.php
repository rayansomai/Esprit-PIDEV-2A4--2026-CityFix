<?php
/**
 * config/database.php
 * Connexion PDO sécurisée — patron Singleton (workshop « Sessions et Sécurité », Partie 2).
 * Une seule connexion est créée puis partagée par toute l'application.
 */

declare(strict_types=1);

class Database
{
    // --- À adapter à votre installation MySQL / phpMyAdmin (XAMPP : root sans mot de passe) ---
    private const HOST    = 'localhost';
    private const DBNAME  = 'cityfix';
    private const USER    = 'root';
    private const PASS    = '';
    private const CHARSET = 'utf8mb4';

    private static ?PDO $pdo = null;

    public static function getConnexion(): PDO
    {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=' . self::HOST . ';dbname=' . self::DBNAME . ';charset=' . self::CHARSET,
                    self::USER,
                    self::PASS,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // une erreur SQL lève une exception
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,                  // requêtes préparées natives (anti-injection SQL)
                    ]
                );
            } catch (PDOException $e) {
                // On n'affiche jamais le détail technique à l'utilisateur.
                error_log('CITYFIX — connexion BDD impossible : ' . $e->getMessage());
                http_response_code(500);
                die('Erreur : impossible de se connecter à la base de données. Vérifiez que MySQL est démarré et que la base « cityfix » est importée.');
            }
        }
        return self::$pdo;
    }
}
