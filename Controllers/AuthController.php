<?php
/**
 * Controllers/AuthController.php
 * Authentification et sécurité (workshop « Sessions et Sécurité », Parties 4 à 7) :
 * inscription, connexion, déconnexion, réinitialisation du mot de passe, sessions,
 * contrôle d'accès (rôles + permissions) et protection CSRF.
 */

declare(strict_types=1);

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/UserModel.php';

class AuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;     // tentatives avant blocage temporaire
    private const LOGIN_LOCK_SECONDS = 60;

    /** Utilisateur connecté, rechargé depuis la base une fois par requête. */
    private static ?UserModel $currentUser = null;
    private static bool $currentUserLoaded = false;
    /** @var string[]|null clés des permissions de l'utilisateur connecté */
    private static ?array $currentPermissions = null;

    // =================================================================
    // INSCRIPTION (Partie 4)
    // =================================================================
    public function register(): void
    {
        if (self::isAuthenticated()) {
            $this->redirectAfterLogin();
        }

        $errors = [];
        $old = [];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!self::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors['global'] = 'Jeton de sécurité invalide. Veuillez réessayer.';
            } else {
                $old = $this->cleanUserInput($_POST);
                $password = (string) ($_POST['password'] ?? '');
                $confirm  = (string) ($_POST['password_confirm'] ?? '');

                $errors = $this->validateIdentity($old)
                        + $this->validatePassword($password, $confirm);

                if ($errors === []) {
                    // Le rôle est imposé par le serveur (citoyen) : jamais lu dans le formulaire.
                    $roleId = $this->findRoleIdByNom('citoyen');

                    $stmt = $this->db->prepare(
                        'INSERT INTO users (role_id, nom, prenom, email, telephone, password, statut)
                         VALUES (:role_id, :nom, :prenom, :email, :telephone, :password, :statut)'
                    );
                    $stmt->execute([
                        'role_id'   => $roleId,
                        'nom'       => $old['nom'],
                        'prenom'    => $old['prenom'],
                        'email'     => $old['email'],
                        'telephone' => $old['telephone'] !== '' ? $old['telephone'] : null,
                        'password'  => password_hash($password, PASSWORD_DEFAULT),   // hachage bcrypt
                        'statut'    => 'actif',
                    ]);

                    $this->flash('success', 'Compte créé avec succès. Vous pouvez maintenant vous connecter.');
                    $this->redirect('login');
                }
            }
        }

        $this->render('register', [
            'title'       => 'Inscription — ' . APP_NAME,
            'currentPage' => 'register',
            'errors'      => $errors,
            'old'         => $old,
        ]);
    }

    // =================================================================
    // CONNEXION (Partie 5)
    // =================================================================
    public function login(): void
    {
        if (self::isAuthenticated()) {
            $this->redirectAfterLogin();
        }

        $error = null;
        $oldEmail = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $oldEmail = mb_strtolower(trim((string) ($_POST['email'] ?? '')), 'UTF-8');
            $password = (string) ($_POST['password'] ?? '');

            if (!self::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $error = 'Jeton de sécurité invalide. Veuillez réessayer.';
            } elseif ($this->isLoginLocked()) {
                $error = 'Trop de tentatives. Patientez une minute avant de réessayer.';
            } elseif ($oldEmail === '' || $password === '') {
                $error = 'Veuillez saisir votre e-mail et votre mot de passe.';
            } else {
                $stmt = $this->db->prepare(
                    'SELECT u.*, r.nom AS role_nom, r.libelle AS role_libelle
                       FROM users u JOIN roles r ON r.id = u.role_id
                      WHERE u.email = :email LIMIT 1'
                );
                $stmt->execute(['email' => $oldEmail]);
                $row = $stmt->fetch();
                $user = $row ? UserModel::fromArray($row) : null;

                // password_verify() recalcule le hachage et compare (jamais de comparaison sur le préfixe)
                if ($user === null || !password_verify($password, $user->getPassword())) {
                    $this->registerFailedLogin();
                    $error = 'E-mail ou mot de passe incorrect.';
                } elseif (!$user->isActif()) {
                    $error = 'Votre compte est désactivé. Contactez l’administration de la plateforme.';
                } else {
                    // Mise à jour du hachage si l'algorithme par défaut de PHP a évolué
                    if (password_needs_rehash($user->getPassword(), PASSWORD_DEFAULT)) {
                        $upd = $this->db->prepare('UPDATE users SET password = :p WHERE id = :id');
                        $upd->execute(['p' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user->getId()]);
                    }

                    // Nouvel identifiant de session : protège contre la fixation de session
                    session_regenerate_id(true);
                    unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);

                    $_SESSION['user_id']       = $user->getId();
                    $_SESSION['role']          = $user->getRoleNom();
                    $_SESSION['username']      = $user->getNomComplet();
                    $_SESSION['last_activity'] = time();

                    self::$currentUserLoaded = false;   // force le rechargement
                    self::$currentPermissions = null;

                    $this->flash('success', 'Bienvenue, ' . $user->getPrenom() . ' !');
                    $this->redirectAfterLogin();
                }
            }
        }

        $this->render('login', [
            'title'       => 'Connexion — ' . APP_NAME,
            'currentPage' => 'login',
            'error'       => $error,
            'oldEmail'    => $oldEmail,
        ]);
    }

    // =================================================================
    // DÉCONNEXION (Partie 5) — en POST avec jeton CSRF
    // =================================================================
    public function logout(): void
    {
        $this->requirePost();
        if (!self::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->redirect('home');
        }
        self::destroySession();

        // Nouvelle session vide uniquement pour afficher le message de confirmation
        session_start();
        $this->flash('success', 'Vous êtes déconnecté.');
        $this->redirect('login');
    }

    /** Vide $_SESSION, supprime le cookie de session et détruit la session côté serveur. */
    public static function destroySession(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        self::$currentUser = null;
        self::$currentUserLoaded = true;
        self::$currentPermissions = [];
    }

    // =================================================================
    // MOT DE PASSE OUBLIÉ / RÉINITIALISATION
    // =================================================================
    public function forgotPassword(): void
    {
        $error = null;
        $sent = false;
        $testLink = null;   // affiché uniquement en mode test local

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')), 'UTF-8');

            if (!self::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $error = 'Jeton de sécurité invalide. Veuillez réessayer.';
            } elseif ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $error = "Veuillez saisir une adresse e-mail valide.";
            } else {
                $stmt = $this->db->prepare("SELECT id, prenom, nom, email FROM users WHERE email = :email AND statut = 'actif' LIMIT 1");
                $stmt->execute(['email' => $email]);
                $row = $stmt->fetch();

                if ($row) {
                    // Jeton aléatoire (64 caractères hexadécimaux) ; seul son SHA-256 est stocké en base
                    $token = bin2hex(random_bytes(32));
                    $upd = $this->db->prepare(
                        'UPDATE users SET reset_token_hash = :h, reset_expires_at = :exp WHERE id = :id'
                    );
                    $upd->execute([
                        'h'   => hash('sha256', $token),
                        'exp' => date('Y-m-d H:i:s', time() + RESET_TOKEN_LIFETIME),
                        'id'  => (int) $row['id'],
                    ]);

                    $link = $this->absoluteUrl(url('reset_password', ['token' => $token]));
                    $this->sendResetLink((string) $row['email'], trim($row['prenom'] . ' ' . $row['nom']), $link);

                    if (MAIL_MODE === 'log' && APP_ENV === 'local') {
                        $testLink = $link;
                    }
                }
                // Même message que l'e-mail existe ou non : on ne révèle pas les comptes existants
                $sent = true;
            }
        }

        $this->render('forgot_password', [
            'title'       => 'Mot de passe oublié — ' . APP_NAME,
            'currentPage' => 'login',
            'error'       => $error,
            'sent'        => $sent,
            'testLink'    => $testLink,
        ]);
    }

    public function resetPassword(): void
    {
        $token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
        $user = $this->findUserByResetToken($token);
        $errors = [];

        if ($user !== null && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!self::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors['global'] = 'Jeton de sécurité invalide. Veuillez réessayer.';
            } else {
                $password = (string) ($_POST['password'] ?? '');
                $errors = $this->validatePassword($password, (string) ($_POST['password_confirm'] ?? ''));

                if ($errors === []) {
                    // Nouveau mot de passe + jeton invalidé (usage unique)
                    $stmt = $this->db->prepare(
                        'UPDATE users SET password = :p, reset_token_hash = NULL, reset_expires_at = NULL WHERE id = :id'
                    );
                    $stmt->execute(['p' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user->getId()]);

                    $this->flash('success', 'Mot de passe modifié. Vous pouvez vous connecter.');
                    $this->redirect('login');
                }
            }
        }

        $this->render('reset_password', [
            'title'       => 'Nouveau mot de passe — ' . APP_NAME,
            'currentPage' => 'login',
            'token'       => $token,
            'validToken'  => $user !== null,
            'errors'      => $errors,
        ]);
    }

    private function findUserByResetToken(string $token): ?UserModel
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        $stmt = $this->db->prepare(
            "SELECT u.*, r.nom AS role_nom, r.libelle AS role_libelle
               FROM users u JOIN roles r ON r.id = u.role_id
              WHERE u.reset_token_hash = :h AND u.reset_expires_at > NOW() AND u.statut = 'actif'
              LIMIT 1"
        );
        $stmt->execute(['h' => hash('sha256', $token)]);
        $row = $stmt->fetch();
        return $row ? UserModel::fromArray($row) : null;
    }

    /** Envoie le lien : écrit dans le journal (mode test) ou par SMTP avec PHPMailer. */
    private function sendResetLink(string $email, string $name, string $link): bool
    {
        if (MAIL_MODE !== 'smtp') {
            $line = sprintf("[%s] À : %s <%s>\nLien de réinitialisation (valable %d min) : %s\n\n",
                date('Y-m-d H:i:s'), $name, $email, intdiv(RESET_TOKEN_LIFETIME, 60), $link);
            return file_put_contents(MAIL_LOG_FILE, $line, FILE_APPEND | LOCK_EX) !== false;
        }

        $configFile = __DIR__ . '/../config/mail.local.php';
        if (!is_file($configFile)) {
            error_log('CITYFIX — config/mail.local.php manquant : e-mail non envoyé.');
            return false;
        }
        $cfg = require $configFile;

        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $cfg['host'];
            $mail->Port       = (int) $cfg['port'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['username'];
            $mail->Password   = $cfg['password'];
            $mail->SMTPSecure = $cfg['encryption'] === 'ssl'
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom($cfg['from_email'], $cfg['from_name']);
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'CITYFIX — Réinitialisation de votre mot de passe';
            $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
            $mail->Body    = '<p>Bonjour ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>'
                           . '<p>Pour choisir un nouveau mot de passe, cliquez sur ce lien (valable 30 minutes) :</p>'
                           . '<p><a href="' . $safeLink . '">' . $safeLink . '</a></p>'
                           . '<p>Si vous n’êtes pas à l’origine de cette demande, ignorez ce message.</p>';
            $mail->AltBody = "Bonjour $name,\nLien de réinitialisation (30 minutes) : $link";
            $mail->send();
            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('CITYFIX — envoi e-mail impossible : ' . $mail->ErrorInfo);
            return false;
        }
    }

    private function absoluteUrl(string $relative): string
    {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $dir   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
        return ($https ? 'https' : 'http') . '://' . $host . $dir . '/' . $relative;
    }

    // =================================================================
    // SESSION ET CONTRÔLE D'ACCÈS (Parties 5 et 6)
    // =================================================================

    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && self::currentUser() !== null;
    }

    /**
     * Utilisateur connecté, RELU EN BASE à chaque requête : un compte supprimé,
     * désactivé ou dont le rôle a changé perd immédiatement ses anciens droits.
     */
    public static function currentUser(): ?UserModel
    {
        if (self::$currentUserLoaded) {
            return self::$currentUser;
        }
        self::$currentUserLoaded = true;
        self::$currentUser = null;

        if (empty($_SESSION['user_id'])) {
            return null;
        }

        $stmt = Database::getConnexion()->prepare(
            'SELECT u.*, r.nom AS role_nom, r.libelle AS role_libelle
               FROM users u JOIN roles r ON r.id = u.role_id
              WHERE u.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => (int) $_SESSION['user_id']]);
        $row = $stmt->fetch();

        if (!$row || $row['statut'] !== 'actif') {
            // Compte supprimé ou désactivé pendant la session : on déconnecte
            $_SESSION = [];
            $_SESSION['flash'][] = ['type' => 'error', 'message' => 'Votre session a été fermée : compte indisponible ou désactivé.'];
            return null;
        }

        self::$currentUser = UserModel::fromArray($row);
        $_SESSION['role'] = self::$currentUser->getRoleNom();   // rôle toujours synchronisé avec la base
        return self::$currentUser;
    }

    /** @return string[] clés des permissions de l'utilisateur connecté */
    public static function permissions(): array
    {
        if (self::$currentPermissions !== null) {
            return self::$currentPermissions;
        }
        $user = self::currentUser();
        if ($user === null) {
            return self::$currentPermissions = [];
        }

        $db = Database::getConnexion();
        if ($user->getRoleNom() === 'admin') {
            // L'administrateur possède toujours toutes les permissions (verrouillé)
            $stmt = $db->query('SELECT cle FROM permissions');
        } else {
            $stmt = $db->prepare(
                'SELECT p.cle FROM permissions p
                   JOIN role_permission rp ON rp.permission_id = p.id
                  WHERE rp.role_id = :role AND p.reserve_admin = 0'
            );
            $stmt->execute(['role' => $user->getRoleId()]);
        }
        return self::$currentPermissions = array_column($stmt->fetchAll(), 'cle');
    }

    public static function hasPermission(string $permission): bool
    {
        return in_array($permission, self::permissions(), true);
    }

    /** Redirige vers la connexion si l'utilisateur n'est pas authentifié (workshop 5.3). */
    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            if (empty($_SESSION['flash'])) {
                $_SESSION['flash'][] = ['type' => 'info', 'message' => 'Veuillez vous connecter pour accéder à cette page.'];
            }
            header('Location: ' . url('login'));
            exit;
        }
    }

    /** Exige une ou plusieurs permissions ; sinon page 403. À appeler AVANT toute opération sensible. */
    public static function requirePermission(string ...$permissions): void
    {
        self::requireAuth();
        foreach ($permissions as $permission) {
            if (!self::hasPermission($permission)) {
                Controller::renderError(403);
            }
        }
    }

    /** Méthodes du workshop (Partie 6.1), conservées pour la compatibilité. */
    public static function hasRole(string $role): bool
    {
        return self::isAuthenticated() && ($_SESSION['role'] ?? null) === $role;
    }

    public static function requireRole(string $role): void
    {
        self::requireAuth();
        if (($_SESSION['role'] ?? null) !== $role) {
            Controller::renderError(403);
        }
    }

    /** Après connexion : Back Office si la permission existe, sinon espace citoyen. */
    private function redirectAfterLogin(): never
    {
        $this->redirect(self::hasPermission('backoffice.access') ? 'dashboard' : 'profile');
    }

    // ---- Limitation des tentatives de connexion (par session) ----
    private function isLoginLocked(): bool
    {
        return isset($_SESSION['login_locked_until']) && time() < (int) $_SESSION['login_locked_until'];
    }

    private function registerFailedLogin(): void
    {
        $_SESSION['login_attempts'] = (int) ($_SESSION['login_attempts'] ?? 0) + 1;
        if ($_SESSION['login_attempts'] >= self::MAX_LOGIN_ATTEMPTS) {
            $_SESSION['login_locked_until'] = time() + self::LOGIN_LOCK_SECONDS;
            $_SESSION['login_attempts'] = 0;
        }
    }

    private function findRoleIdByNom(string $nom): int
    {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE nom = :nom LIMIT 1');
        $stmt->execute(['nom' => $nom]);
        return (int) $stmt->fetchColumn();
    }

    // =================================================================
    // CSRF (Partie 7.4)
    // =================================================================
    public static function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(?string $token): bool
    {
        if (!$token || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
