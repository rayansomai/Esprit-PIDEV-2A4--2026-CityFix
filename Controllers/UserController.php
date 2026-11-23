<?php
/**
 * Controllers/UserController.php
 * Gestion des utilisateurs :
 *  - Front Office : profil personnel, modification des informations et du mot de passe ;
 *  - Back Office  : liste (recherche, filtres, pagination), détail, création, modification,
 *                   activation/désactivation, suppression.
 * Chaque action vérifie les permissions CÔTÉ SERVEUR avant toute opération.
 * Règle métier : le dernier administrateur actif ne peut être ni supprimé, ni désactivé,
 * ni rétrogradé ; un administrateur ne peut pas modifier son propre rôle/statut ni se supprimer.
 */

declare(strict_types=1);

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/RoleModel.php';

class UserController extends Controller
{
    private const STATUTS = ['actif', 'inactif'];

    // =================================================================
    // FRONT OFFICE — PROFIL
    // =================================================================
    public function profile(array $errors = [], array $old = []): void
    {
        AuthController::requirePermission('profile.manage');

        $this->render('profile', [
            'title'       => 'Mon profil — ' . APP_NAME,
            'currentPage' => 'profile',
            'user'        => AuthController::currentUser(),
            'errors'      => $errors,
            'old'         => $old,
        ]);
    }

    public function updateProfile(): void
    {
        $this->requirePost();
        AuthController::requirePermission('profile.manage');
        $this->checkCsrfOrRedirect('profile');

        $me = AuthController::currentUser();
        $data = $this->cleanUserInput($_POST);
        $errors = $this->validateIdentity($data, $me->getId());

        if ($errors !== []) {
            $this->profile($errors, $data);
            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE users SET nom = :nom, prenom = :prenom, email = :email, telephone = :tel WHERE id = :id'
        );
        $stmt->execute([
            'nom'    => $data['nom'],
            'prenom' => $data['prenom'],
            'email'  => $data['email'],
            'tel'    => $data['telephone'] !== '' ? $data['telephone'] : null,
            'id'     => $me->getId(),
        ]);
        $_SESSION['username'] = $data['prenom'] . ' ' . $data['nom'];

        $this->flash('success', 'Profil mis à jour avec succès.');
        $this->redirect('profile');
    }

    public function updatePassword(): void
    {
        $this->requirePost();
        AuthController::requirePermission('profile.manage');
        $this->checkCsrfOrRedirect('profile');

        $me = AuthController::currentUser();
        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['new_password'] ?? '');
        $errors  = [];

        if (!password_verify($current, $me->getPassword())) {
            $errors['current_password'] = 'Le mot de passe actuel est incorrect.';
        } else {
            $errors = $this->validatePassword($new, (string) ($_POST['new_password_confirm'] ?? ''), 'new_password');
        }

        if ($errors !== []) {
            $this->profile($errors);
            return;
        }

        $stmt = $this->db->prepare('UPDATE users SET password = :p WHERE id = :id');
        $stmt->execute(['p' => password_hash($new, PASSWORD_DEFAULT), 'id' => $me->getId()]);
        session_regenerate_id(true);

        $this->flash('success', 'Mot de passe modifié avec succès.');
        $this->redirect('profile');
    }

    // =================================================================
    // BACK OFFICE — LISTE, RECHERCHE, FILTRES, PAGINATION
    // =================================================================
    public function index(): void
    {
        AuthController::requirePermission('backoffice.access', 'users.view');

        $filters = [
            'q'      => trim((string) ($_GET['q'] ?? '')),
            'role'   => (string) ($_GET['role'] ?? ''),
            'statut' => (string) ($_GET['statut'] ?? ''),
        ];

        // Construction dynamique du WHERE avec des paramètres nommés (requête préparée)
        $where = [];
        $params = [];
        if ($filters['q'] !== '') {
            $where[] = '(u.nom LIKE :q1 OR u.prenom LIKE :q2 OR u.email LIKE :q3 OR CONCAT(u.prenom, \' \', u.nom) LIKE :q4)';
            $like = '%' . addcslashes($filters['q'], '%_\\') . '%';
            $params += ['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like];
        }
        if (in_array($filters['role'], ['citoyen', 'agent', 'admin'], true)) {
            $where[] = 'r.nom = :role';
            $params['role'] = $filters['role'];
        } else {
            $filters['role'] = '';
        }
        if (in_array($filters['statut'], self::STATUTS, true)) {
            $where[] = 'u.statut = :statut';
            $params['statut'] = $filters['statut'];
        } else {
            $filters['statut'] = '';
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $count = $this->db->prepare("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id $whereSql");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $pages = max(1, (int) ceil($total / USERS_PER_PAGE));
        $page  = min(max(1, $this->intParam('page', $_GET) ?: 1), $pages);
        $offset = ($page - 1) * USERS_PER_PAGE;

        $stmt = $this->db->prepare(
            "SELECT u.*, r.nom AS role_nom, r.libelle AS role_libelle
               FROM users u JOIN roles r ON r.id = u.role_id
               $whereSql
           ORDER BY u.created_at DESC, u.id DESC
              LIMIT " . USERS_PER_PAGE . " OFFSET $offset"
        );
        $stmt->execute($params);
        $users = array_map([UserModel::class, 'fromArray'], $stmt->fetchAll());

        $this->render('admin/users/index', [
            'title'       => 'Utilisateurs — Back Office ' . APP_NAME,
            'currentPage' => 'users',
            'users'       => $users,
            'filters'     => $filters,
            'total'       => $total,
            'page'        => $page,
            'pages'       => $pages,
            'offset'      => $offset,
            'roles'       => $this->findAllRoles(),
        ], 'layout_admin');
    }

    // =================================================================
    // BACK OFFICE — DÉTAIL
    // =================================================================
    public function show(): void
    {
        AuthController::requirePermission('backoffice.access', 'users.view');
        $user = $this->findUserOr404($this->intParam('id', $_GET));

        $this->render('admin/users/show', [
            'title'       => $user->getNomComplet() . ' — Back Office ' . APP_NAME,
            'currentPage' => 'users',
            'user'        => $user,
            'isLastAdmin' => $this->isLastActiveAdmin($user),
            'isSelf'      => $user->getId() === AuthController::currentUser()->getId(),
        ], 'layout_admin');
    }

    // =================================================================
    // BACK OFFICE — CRÉATION
    // =================================================================
    public function create(array $errors = [], array $old = []): void
    {
        AuthController::requirePermission('backoffice.access', 'users.create');

        $this->render('admin/users/create', [
            'title'       => 'Nouvel utilisateur — Back Office ' . APP_NAME,
            'currentPage' => 'users',
            'roles'       => $this->findAllRoles(),
            'errors'      => $errors,
            'old'         => $old + ['role_id' => '', 'statut' => 'actif'],
        ], 'layout_admin');
    }

    public function store(): void
    {
        $this->requirePost();
        AuthController::requirePermission('backoffice.access', 'users.create');
        $this->checkCsrfOrRedirect('user_create');

        $data = $this->cleanUserInput($_POST) + $this->readAccessFields($_POST);
        $errors = $this->validateIdentity($data)
                + $this->validateAccess($data)
                + $this->validatePassword((string) ($_POST['password'] ?? ''), (string) ($_POST['password_confirm'] ?? ''));

        if ($errors !== []) {
            $this->create($errors, $data);
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO users (role_id, nom, prenom, email, telephone, password, statut)
             VALUES (:role_id, :nom, :prenom, :email, :tel, :password, :statut)'
        );
        $stmt->execute([
            'role_id'  => $data['role_id'],
            'nom'      => $data['nom'],
            'prenom'   => $data['prenom'],
            'email'    => $data['email'],
            'tel'      => $data['telephone'] !== '' ? $data['telephone'] : null,
            'password' => password_hash((string) $_POST['password'], PASSWORD_DEFAULT),
            'statut'   => $data['statut'],
        ]);

        $this->flash('success', 'Utilisateur « ' . $data['prenom'] . ' ' . $data['nom'] . ' » créé avec succès.');
        $this->redirect('users');
    }

    // =================================================================
    // BACK OFFICE — MODIFICATION
    // =================================================================
    public function edit(array $errors = [], array $old = []): void
    {
        AuthController::requirePermission('backoffice.access', 'users.edit');
        $user = $this->findUserOr404($this->intParam('id', $old ?: $_GET));
        $this->denyIfAdminTargetWithoutAdminRole($user);

        $this->render('admin/users/edit', [
            'title'       => 'Modifier ' . $user->getNomComplet() . ' — Back Office ' . APP_NAME,
            'currentPage' => 'users',
            'user'        => $user,
            'roles'       => $this->findAllRoles(),
            'errors'      => $errors,
            'old'         => $old ?: [
                'nom' => $user->getNom(), 'prenom' => $user->getPrenom(), 'email' => $user->getEmail(),
                'telephone' => (string) $user->getTelephone(), 'role_id' => $user->getRoleId(), 'statut' => $user->getStatut(),
            ],
            'lockAccess'  => $this->isLastActiveAdmin($user) || $user->getId() === AuthController::currentUser()->getId(),
            'isLastAdmin' => $this->isLastActiveAdmin($user),
        ], 'layout_admin');
    }

    public function update(): void
    {
        $this->requirePost();
        AuthController::requirePermission('backoffice.access', 'users.edit');
        $id = $this->intParam('id', $_POST);
        $this->checkCsrfOrRedirect('user_edit', ['id' => $id]);

        $user = $this->findUserOr404($id);
        $this->denyIfAdminTargetWithoutAdminRole($user);

        $data = $this->cleanUserInput($_POST) + $this->readAccessFields($_POST) + ['id' => $id];
        $errors = $this->validateIdentity($data, $id) + $this->validateAccess($data);

        // Règles de protection du rôle et du statut
        $roleChanges   = $data['role_id'] !== $user->getRoleId();
        $statutChanges = $data['statut'] !== $user->getStatut();
        if ($roleChanges || $statutChanges) {
            if ($user->getId() === AuthController::currentUser()->getId()) {
                $errors['role_id'] = 'Vous ne pouvez pas modifier votre propre rôle ni votre propre statut.';
            } elseif ($this->isLastActiveAdmin($user)) {
                $errors['role_id'] = 'Action refusée : c’est le dernier administrateur actif (rétrogradation et désactivation impossibles).';
            }
        }

        $newPassword = (string) ($_POST['password'] ?? '');
        if ($newPassword !== '') {
            $errors += $this->validatePassword($newPassword, (string) ($_POST['password_confirm'] ?? ''));
        }

        if ($errors !== []) {
            $this->edit($errors, $data);
            return;
        }

        $sql = 'UPDATE users SET nom = :nom, prenom = :prenom, email = :email, telephone = :tel,
                       role_id = :role_id, statut = :statut';
        $params = [
            'nom' => $data['nom'], 'prenom' => $data['prenom'], 'email' => $data['email'],
            'tel' => $data['telephone'] !== '' ? $data['telephone'] : null,
            'role_id' => $data['role_id'], 'statut' => $data['statut'], 'id' => $id,
        ];
        if ($newPassword !== '') {
            $sql .= ', password = :password';
            $params['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        $stmt = $this->db->prepare($sql . ' WHERE id = :id');
        $stmt->execute($params);

        $this->flash('success', 'Utilisateur mis à jour avec succès.');
        $this->redirect('user_show', ['id' => $id]);
    }

    // =================================================================
    // BACK OFFICE — ACTIVER / DÉSACTIVER
    // =================================================================
    public function toggle(): void
    {
        $this->requirePost();
        AuthController::requirePermission('backoffice.access', 'users.toggle');
        $this->checkCsrfOrRedirect('users');

        $user = $this->findUserOr404($this->intParam('id', $_POST));
        $this->denyIfAdminTargetWithoutAdminRole($user);

        if ($user->getId() === AuthController::currentUser()->getId()) {
            $this->flash('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        } elseif ($user->isActif() && $this->isLastActiveAdmin($user)) {
            $this->flash('error', 'Action refusée : impossible de désactiver le dernier administrateur actif.');
        } else {
            $newStatut = $user->isActif() ? 'inactif' : 'actif';
            $stmt = $this->db->prepare('UPDATE users SET statut = :s WHERE id = :id');
            $stmt->execute(['s' => $newStatut, 'id' => $user->getId()]);
            $this->flash('success', 'Compte de ' . $user->getNomComplet() . ($newStatut === 'actif' ? ' activé.' : ' désactivé.'));
        }
        $this->redirectBack();
    }

    // =================================================================
    // BACK OFFICE — SUPPRESSION
    // =================================================================
    public function delete(): void
    {
        $this->requirePost();
        AuthController::requirePermission('backoffice.access', 'users.delete');
        $this->checkCsrfOrRedirect('users');

        $user = $this->findUserOr404($this->intParam('id', $_POST));
        $this->denyIfAdminTargetWithoutAdminRole($user);

        if ($user->getId() === AuthController::currentUser()->getId()) {
            $this->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            $this->redirectBack();
        }
        if ($this->isLastActiveAdmin($user)) {
            $this->flash('error', 'Action refusée : impossible de supprimer le dernier administrateur actif.');
            $this->redirectBack();
        }

        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $user->getId()]);
        $this->flash('success', 'Utilisateur « ' . $user->getNomComplet() . ' » supprimé.');
        $this->redirect('users');
    }

    // =================================================================
    // OUTILS
    // =================================================================

    private function findUserOr404(int $id): UserModel
    {
        $user = $id > 0 ? $this->findUserById($id) : null;
        if ($user === null) {
            Controller::renderError(404);
        }
        return $user;
    }

    /** Vrai si l'utilisateur est un administrateur actif ET le seul dans ce cas. */
    private function isLastActiveAdmin(UserModel $user): bool
    {
        if ($user->getRoleNom() !== 'admin' || !$user->isActif()) {
            return false;
        }
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id
              WHERE r.nom = 'admin' AND u.statut = 'actif'"
        );
        return (int) $stmt->fetchColumn() <= 1;
    }

    /**
     * Seul un administrateur peut agir sur un compte administrateur ou attribuer le rôle admin,
     * même si l'administrateur a donné des droits de gestion à un autre rôle.
     */
    private function denyIfAdminTargetWithoutAdminRole(UserModel $target): void
    {
        if ($target->getRoleNom() === 'admin' && AuthController::currentUser()->getRoleNom() !== 'admin') {
            Controller::renderError(403);
        }
    }

    private function readAccessFields(array $post): array
    {
        return [
            'role_id' => (int) ($post['role_id'] ?? 0),
            'statut'  => (string) ($post['statut'] ?? ''),
        ];
    }

    private function validateAccess(array $data): array
    {
        $errors = [];
        $stmt = $this->db->prepare('SELECT nom FROM roles WHERE id = :id');
        $stmt->execute(['id' => $data['role_id']]);
        $roleNom = $stmt->fetchColumn();

        if ($roleNom === false) {
            $errors['role_id'] = 'Veuillez choisir un rôle valide.';
        } elseif ($roleNom === 'admin' && AuthController::currentUser()->getRoleNom() !== 'admin') {
            $errors['role_id'] = 'Seul un administrateur peut attribuer le rôle administrateur.';
        }
        if (!in_array($data['statut'], self::STATUTS, true)) {
            $errors['statut'] = 'Statut invalide.';
        }
        return $errors;
    }

    /** Retourne à la page précédente (liste ou fiche) en restant dans l'application. */
    private function redirectBack(): never
    {
        $back = (string) ($_POST['back'] ?? '');
        if ($back === 'show') {
            $this->redirect('user_show', ['id' => $this->intParam('id', $_POST)]);
        }
        $this->redirect('users');
    }
}
