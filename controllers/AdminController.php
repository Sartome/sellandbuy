<?php
// controllers/AdminController.php

class AdminController {
    public function index() {
        requireAdmin();
        $pageTitle = 'Administration';
        require_once VIEWS_PATH . '/admin/index.php';
    }
}


