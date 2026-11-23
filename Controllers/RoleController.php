<?php
/**
 * Controllers/RoleController.php
 * Rôles et permissions (réservé à l'administrateur).
 * Les trois rôles sont prédéfinis : pas de création ni de suppression de rôle.
 * L'administrateur attribue les permissions des rôles « citoyen » et « agent ».
 * Les permissions du rôle « admin » sont verrouillées (il garde toujours tous les droits).
 */

declare(strict_types=1);

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/RoleModel.php';
require_once __DIR__ . '/../Models/PermissionModel.php';

class RoleController extends Controller
{
    /** Permissions qui nécessitent l'accès au Back Office et la consultation des utilisateurs. */
    private const USER_MANAGEMENT = ['users.create', 'users.edit', 'users.toggle', 'users.delete'];

    public function index(): void
    {
        AuthController::requirePermission('backoffice.access', 'roles.manage');

        $permissions = array_map(
            [PermissionModel::class, 'fromArray'],
            $this->db->query('SELECT * FROM permissions ORDER BY id')->fetchAll()
        );

        // Matrice : [role_id => [permission_id, ...]]
        $matrix = [];
        foreach ($this->db->query('SELECT role_id, permission_id FROM role_permission')->fetchAll() as $row) {
            $matrix[(int) $row['role_id']][] = (int) $row['permission_id'];
        }

        $this->render('admin/roles/index', [
            'title'       => 'Rôles et permissions — Back Office ' . APP_NAME,
            'currentPage' => 'roles',
            'roles'       => $this->findAllRoles(),
            'permissionList' => $permissions,   // ($permissions est réservé aux droits de l'utilisateur connecté)
            'matrix'      => $matrix,
        ], 'layout_admin');
    }

    public function update(): void
    {
        $this->requirePost();
        AuthController::requirePermission('backoffice.access', 'roles.manage');
        $this->checkCsrfOrRedirect('roles');

        // Permissions attribuables (hors permissions réservées à l'admin), indexées par clé
        $assignable = [];
        foreach ($this->db->query('SELECT id, cle FROM permissions WHERE reserve_admin = 0')->fetchAll() as $row) {
            $assignable[$row['cle']] = (int) $row['id'];
        }
        $posted = $_POST['perms'] ?? [];
        if (!is_array($posted)) {
            $posted = [];
        }

        $this->db->beginTransaction();
        try {
            foreach ($this->findAllRoles() as $role) {
                if ($role->getNom() === 'admin') {
                    continue;   // rôle administrateur verrouillé
                }

                $selectedKeys = [];
                foreach ((array) ($posted[$role->getId()] ?? []) as $key) {
                    if (is_string($key) && isset($assignable[$key])) {
                        $selectedKeys[] = $key;
                    }
                }
                // Cohérence : gérer les utilisateurs implique d'accéder au Back Office et de les voir
                if (array_intersect($selectedKeys, self::USER_MANAGEMENT)) {
                    $selectedKeys[] = 'users.view';
                }
                if (in_array('users.view', $selectedKeys, true)) {
                    $selectedKeys[] = 'backoffice.access';
                }
                $selectedKeys = array_unique($selectedKeys);

                $del = $this->db->prepare('DELETE FROM role_permission WHERE role_id = :r');
                $del->execute(['r' => $role->getId()]);

                $ins = $this->db->prepare('INSERT INTO role_permission (role_id, permission_id) VALUES (:r, :p)');
                foreach ($selectedKeys as $key) {
                    $ins->execute(['r' => $role->getId(), 'p' => $assignable[$key]]);
                }
            }
            $this->db->commit();
            $this->flash('success', 'Permissions enregistrées.');
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('CITYFIX — mise à jour des permissions : ' . $e->getMessage());
            $this->flash('error', 'Erreur lors de l’enregistrement des permissions.');
        }

        $this->redirect('roles');
    }
}
