<?php

class TicketController {
    public function index() {
        requireLogin();
        require_once MODELS_PATH . '/Ticket.php';

        $ticketModel = new Ticket();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject = trim(sanitize($_POST['subject'] ?? ''));
            $message = trim(sanitize($_POST['message'] ?? ''));

            if ($subject === '' || $message === '') {
                $error = 'Veuillez remplir le sujet et le message';
            } else {
                if ($ticketModel->create($userId, $subject, $message)) {
                    redirect('/index.php?controller=ticket&action=index', 'Ticket envoyé à l\'administration');
                } else {
                    $error = 'Erreur lors de l\'envoi du ticket';
                }
            }
        }

        $tickets = $ticketModel->getByUser($userId);
        $pageTitle = 'Centre de tickets';
        require_once VIEWS_PATH . '/tickets/index.php';
    }
}
