<?php
/**
 * Controllers/DashboardController.php
 * Tableau de bord du Back Office : statistiques des comptes et derniers inscrits.
 */

declare(strict_types=1);

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/UserModel.php';

class DashboardController extends Controller
{
    public function index(): void
    {
        AuthController::requirePermission('backoffice.access');

        $byRole = ['citoyen' => 0, 'agent' => 0, 'admin' => 0];
        $rows = $this->db->query(
            'SELECT r.nom, COUNT(u.id) AS total
               FROM roles r LEFT JOIN users u ON u.role_id = r.id
           GROUP BY r.id, r.nom'
        )->fetchAll();
        foreach ($rows as $row) {
            $byRole[$row['nom']] = (int) $row['total'];
        }

        $inactifs = (int) $this->db->query("SELECT COUNT(*) FROM users WHERE statut = 'inactif'")->fetchColumn();
        $total = array_sum($byRole);

        $latest = [];
        if (AuthController::hasPermission('users.view')) {
            $latest = array_map([UserModel::class, 'fromArray'], $this->db->query(
                'SELECT u.*, r.nom AS role_nom, r.libelle AS role_libelle
                   FROM users u JOIN roles r ON r.id = u.role_id
               ORDER BY u.created_at DESC, u.id DESC
                  LIMIT 5'
            )->fetchAll());
        }

        $this->render('admin/dashboard', [
            'title'       => 'Tableau de bord — Back Office ' . APP_NAME,
            'currentPage' => 'dashboard',
            'total'       => $total,
            'byRole'      => $byRole,
            'inactifs'    => $inactifs,
            'latest'      => $latest,
        ], 'layout_admin');
    }
}
