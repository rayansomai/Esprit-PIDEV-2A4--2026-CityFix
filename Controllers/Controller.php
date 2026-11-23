<?php
/**
 * Controllers/Controller.php
 * Contrôleur de base (atelier « Intégration Template », Partie 2 — étape 8) :
 *  - capture le contenu d'une vue dans $content, puis inclut le layout (Front ou Back Office) ;
 *  - fournit les outils communs : redirection, messages flash, validation des formulaires.
 * Tous les contrôleurs de l'application héritent de cette classe.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Views/includes/icons.php';         // fonction icon() utilisée par les vues
require_once __DIR__ . '/../Views/includes/form_helpers.php';  // affichage des erreurs de formulaire

abstract class Controller
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnexion();
    }

    // ------------------------------------------------------------
    // AFFICHAGE
    // ------------------------------------------------------------

    /**
     * Affiche une vue dans un layout.
     * @param string $view   chemin de la vue sous Views/ sans « .php » (ex : 'admin/users/index')
     * @param array  $data   variables transmises à la vue (title, currentPage, ...)
     * @param string $layout 'layout' (Front Office) ou 'layout_admin' (Back Office)
     */
    protected function render(string $view, array $data = [], string $layout = 'layout'): void
    {
        // Variables communes disponibles dans toutes les vues et layouts
        $data['authUser']    = AuthController::currentUser();
        $data['permissions'] = AuthController::permissions();
        $data['csrfToken']   = AuthController::getCsrfToken();
        $data['flash']       = $this->pullFlash();
        $data['title']       = $data['title'] ?? APP_NAME;
        $data['currentPage'] = $data['currentPage'] ?? '';

        extract($data, EXTR_SKIP);

        ob_start();
        require __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/' . $layout . '.php';
    }

    /** Affiche une page d'erreur (403 accès refusé, 404 introuvable) puis arrête le script. */
    public static function renderError(int $code): never
    {
        http_response_code($code);
        $controller = new HomeController();
        $controller->render((string) $code, [
            'title'       => ($code === 403 ? 'Accès refusé' : 'Page introuvable') . ' — ' . APP_NAME,
            'currentPage' => '',
        ]);
        exit;
    }

    // ------------------------------------------------------------
    // NAVIGATION
    // ------------------------------------------------------------

    protected function redirect(string $action, array $params = []): never
    {
        header('Location: ' . url($action, $params));
        exit;
    }

    /** Les actions qui modifient des données n'acceptent que POST (jamais un simple lien). */
    protected function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Méthode non autorisée.');
        }
    }

    /** Vérifie le jeton CSRF d'un formulaire ; en cas d'échec, message + retour à la page indiquée. */
    protected function checkCsrfOrRedirect(string $action, array $params = []): void
    {
        if (!AuthController::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Jeton de sécurité invalide ou expiré. Veuillez réessayer.');
            $this->redirect($action, $params);
        }
    }

    // ------------------------------------------------------------
    // MESSAGES FLASH (affichés une seule fois, à la page suivante)
    // ------------------------------------------------------------

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    private function pullFlash(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }

    // ------------------------------------------------------------
    // VALIDATION DES SAISIES (côté serveur — toujours exécutée)
    // ------------------------------------------------------------

    /** Nettoie les champs texte d'un utilisateur (espaces superflus). */
    protected function cleanUserInput(array $post): array
    {
        return [
            'nom'       => trim((string) ($post['nom'] ?? '')),
            'prenom'    => trim((string) ($post['prenom'] ?? '')),
            'email'     => mb_strtolower(trim((string) ($post['email'] ?? '')), 'UTF-8'),
            'telephone' => preg_replace('/\s+/', '', (string) ($post['telephone'] ?? '')) ?? '',
        ];
    }

    /**
     * Valide nom, prénom, e-mail (format + unicité) et téléphone.
     * @return array<string,string> erreurs indexées par nom de champ
     */
    protected function validateIdentity(array $data, ?int $ignoreUserId = null): array
    {
        $errors = [];
        $namePattern = "/^[\p{L}][\p{L}\s'\-]{1,49}$/u";   // lettres, espaces, apostrophes, tirets

        foreach (['nom' => 'Le nom', 'prenom' => 'Le prénom'] as $field => $label) {
            $value = $data[$field];
            if ($value === '') {
                $errors[$field] = $label . ' est obligatoire.';
            } elseif (mb_strlen($value, 'UTF-8') < 2 || mb_strlen($value, 'UTF-8') > 50) {
                $errors[$field] = $label . ' doit contenir entre 2 et 50 caractères.';
            } elseif (!preg_match($namePattern, $value)) {
                $errors[$field] = $label . ' ne peut contenir que des lettres, espaces, apostrophes et tirets.';
            }
        }

        if ($data['email'] === '') {
            $errors['email'] = "L'adresse e-mail est obligatoire.";
        } elseif (mb_strlen($data['email'], 'UTF-8') > 150 || filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = "Format d'adresse e-mail invalide.";
        } elseif ($this->emailExists($data['email'], $ignoreUserId)) {
            $errors['email'] = 'Cette adresse e-mail est déjà utilisée.';
        }

        if ($data['telephone'] !== '' && !preg_match('/^\+?[0-9]{8,15}$/', $data['telephone'])) {
            $errors['telephone'] = 'Le téléphone doit contenir entre 8 et 15 chiffres.';
        }

        return $errors;
    }

    /**
     * Règles du mot de passe : 8 à 72 caractères, une majuscule, une minuscule, un chiffre.
     * (72 = limite de l'algorithme bcrypt utilisé par password_hash)
     */
    protected function validatePassword(string $password, string $confirm, string $field = 'password'): array
    {
        $errors = [];
        if ($password === '') {
            $errors[$field] = 'Le mot de passe est obligatoire.';
        } elseif (strlen($password) < 8 || strlen($password) > 72) {
            $errors[$field] = 'Le mot de passe doit contenir entre 8 et 72 caractères.';
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[$field] = 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.';
        } elseif ($password !== $confirm) {
            $errors[$field . '_confirm'] = 'Les deux mots de passe ne correspondent pas.';
        }
        return $errors;
    }

    // ------------------------------------------------------------
    // ACCÈS AUX DONNÉES COMMUNS (requêtes préparées)
    // ------------------------------------------------------------

    protected function emailExists(string $email, ?int $ignoreUserId = null): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
        $stmt->execute(['email' => $email, 'id' => $ignoreUserId ?? 0]);
        return (bool) $stmt->fetch();
    }

    /** Charge un utilisateur avec son rôle (jointure users -> roles). */
    protected function findUserById(int $id): ?UserModel
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.nom AS role_nom, r.libelle AS role_libelle
               FROM users u
               JOIN roles r ON r.id = u.role_id
              WHERE u.id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? UserModel::fromArray($row) : null;
    }

    /** @return RoleModel[] */
    protected function findAllRoles(): array
    {
        $stmt = $this->db->query(
            'SELECT r.*, COUNT(u.id) AS nb_users
               FROM roles r
          LEFT JOIN users u ON u.role_id = r.id
           GROUP BY r.id
           ORDER BY r.id'
        );
        return array_map([RoleModel::class, 'fromArray'], $stmt->fetchAll());
    }

    /** Lit un entier positif dans $_GET (ex : ?id=5). */
    protected function intParam(string $key, array $source): int
    {
        $value = filter_var($source[$key] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $value === false || $value === null ? 0 : (int) $value;
    }
}
