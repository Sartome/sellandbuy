<?php
// models/Facture.php

class Facture {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(string $dateFacture, string $pdfPath): int {
        $stmt = $this->db->prepare("INSERT INTO Facture (date_facture, pdf_facture) VALUES (?, ?)");
        $stmt->execute([$dateFacture, $pdfPath]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM Facture WHERE id_facture = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM Facture ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM Facture WHERE id_facture = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Convenience: generate PDF using InvoicePdf helper and create DB entry
     */
    public function generateAndStore(array $invoiceData): ?int {
        require_once HELPERS_PATH . '/InvoicePdf.php';
        $pdfHelper = new InvoicePdf();

        $pdfPath = $pdfHelper->generatePdf($invoiceData);
        if (!$pdfPath) return null;

        $date = $invoiceData['date'] ?? date('Y-m-d');
        return $this->create($date, $pdfPath);
    }
}
?>