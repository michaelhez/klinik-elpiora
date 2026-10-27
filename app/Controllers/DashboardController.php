<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;

class DashboardController extends Controller
{
    public function index(Request $request): never
    {
        $pdo = Database::connection();

        $totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL')->fetchColumn();
        $totalRoles = (int) $pdo->query('SELECT COUNT(*) FROM roles WHERE deleted_at IS NULL')->fetchColumn();
        $totalPermissions = (int) $pdo->query('SELECT COUNT(*) FROM permissions')->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $todayActivity = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 8');
        $stmt->execute();
        $recentActivity = $stmt->fetchAll();

        $this->view('dashboard.index', [
            'title' => 'Dashboard',
            'totalUsers' => $totalUsers,
            'totalRoles' => $totalRoles,
            'totalPermissions' => $totalPermissions,
            'todayActivity' => $todayActivity,
            'recentActivity' => $recentActivity,
        ]);
    }
}
