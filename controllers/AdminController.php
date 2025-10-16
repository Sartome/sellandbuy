<?php
// controllers/AdminController.php

class AdminController {
    public function index() {
        requireAdmin();
        $pageTitle = 'Administration';
        require_once VIEWS_PATH . '/admin/index.php';
    }

    public function analytics() {
        requireAdmin();
        require_once MODELS_PATH . '/Sale.php';
        require_once MODELS_PATH . '/Database.php';

        $sale = new Sale();
        $salesCount = $sale->countByDayLast30();
        $salesAmount = $sale->totalAmountByDayLast30();

        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->query("SELECT DATE(created_at) d, COUNT(*) c FROM Utilisateur WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY d ORDER BY d");
        $usersCount = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Analyses';
        require_once VIEWS_PATH . '/admin/analytics.php';
    }
}


