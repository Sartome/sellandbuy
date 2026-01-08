<?php

require_once(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php');

class InvoicePdf {
    private $pdf;
    private $width = 210; // A4 width in mm
    private $margin = 15; // Margin in mm
    private $currentY;
    private $lineHeight = 7;
    private $fontSize = 10;
    private $smallFontSize = 8;
    private $titleFontSize = 12;
    private $headerFontSize = 14;
    private $colorPrimary = array(59, 130, 246); // blue-500
    private $colorGray = array(229, 231, 235); // gray-200

    public function __construct() {
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->pdf->SetCreator('SellAndBuy');
        $this->pdf->SetAuthor('SellAndBuy');
        $this->pdf->SetTitle('Facture');
        $this->pdf->SetMargins($this->margin, $this->margin, $this->margin);
        $this->pdf->SetAutoPageBreak(true, $this->margin);
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
    }

    public function outputInvoice(array $sale, array $product = null, array $buyer = null, array $seller = null) {
        $this->pdf->AddPage();
        $this->currentY = $this->margin;

        // Header
        $this->addHeader($seller);
        $this->currentY = $this->pdf->GetY() + 10;

        // Invoice Info
        $this->addInvoiceInfo($sale);
        $this->currentY = $this->pdf->GetY() + 5;

        // Buyer Info
        $this->addBuyerInfo($buyer);
        $this->currentY = $this->pdf->GetY() + 10;

        // Items Table
        $this->addItemsTable($product, $sale);
        $this->currentY = $this->pdf->GetY() + 5;

        // Payment Info
        $this->addPaymentInfo($sale);

        // Output
        $this->pdf->Output('facture-' . ($sale['id'] ?? '0') . '.pdf', 'I');
    }

    private function addHeader($seller) {
        // Logo
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/public/images/logo.png';
        if (file_exists($logoPath)) {
            $this->pdf->Image($logoPath, $this->margin, $this->currentY, 50);
        }

        // Company Info
        $this->pdf->SetFont('helvetica', 'B', $this->headerFontSize);
        $this->pdf->SetXY($this->width - 100, $this->currentY);
        $this->pdf->Cell(80, 8, 'FACTURE', 0, 1, 'R');
        
        $this->pdf->SetFont('helvetica', '', $this->fontSize);
        $this->pdf->SetXY($this->width - 100, $this->currentY + 10);
        $this->pdf->MultiCell(80, 5, 
            "SellAndBuy\n" .
            ($seller['nom_entreprise'] ?? 'Vendeur') . "\n" .
            ($seller['adresse'] ?? '') . "\n" .
            (!empty($seller['code_postal']) ? $seller['code_postal'] . ' ' : '') . 
            ($seller['ville'] ?? '') . "\n" .
            ($seller['email'] ?? '') . "\n" .
            (!empty($seller['telephone']) ? 'Tél: ' . $seller['telephone'] : ''),
            0, 'R', false, 0, '', true, 0, false, true, 0, 'T', false
        );
        
        $this->currentY = $this->pdf->GetY();
    }

    private function addInvoiceInfo($sale) {
        $this->pdf->SetFont('helvetica', 'B', $this->fontSize);
        $this->pdf->SetXY($this->margin, $this->currentY);
        $this->pdf->Cell(50, 5, 'Facture n°' . ($sale['id'] ?? ''), 0, 1);
        
        $this->pdf->SetFont('helvetica', '', $this->fontSize);
        $this->pdf->SetX($this->margin);
        $this->pdf->Cell(50, 5, 'Date: ' . date('d/m/Y', strtotime($sale['created_at'] ?? 'now')), 0, 1);
        
        if (isset($sale['reference'])) {
            $this->pdf->SetX($this->margin);
            $this->pdf->Cell(50, 5, 'Référence: ' . $sale['reference'], 0, 1);
        }
        
        $this->currentY = $this->pdf->GetY();
    }

    private function addBuyerInfo($buyer) {
        $this->pdf->SetFont('helvetica', 'B', $this->fontSize);
        $this->pdf->SetXY($this->margin, $this->currentY);
        $this->pdf->Cell(100, 5, 'Client', 0, 1);
        
        $this->pdf->SetFont('helvetica', '', $this->fontSize);
        $this->pdf->SetX($this->margin);
        $buyerInfo = [];
        if (!empty($buyer['prenom']) || !empty($buyer['nom'])) {
            $buyerInfo[] = trim(($buyer['prenom'] ?? '') . ' ' . ($buyer['nom'] ?? ''));
        }
        if (!empty($buyer['adresse'])) {
            $buyerInfo[] = $buyer['adresse'];
        }
        if (!empty($buyer['code_postal']) || !empty($buyer['ville'])) {
            $buyerInfo[] = trim(($buyer['code_postal'] ?? '') . ' ' . ($buyer['ville'] ?? ''));
        }
        if (!empty($buyer['email'])) {
            $buyerInfo[] = $buyer['email'];
        }
        if (!empty($buyer['telephone'])) {
            $buyerInfo[] = 'Tél: ' . $buyer['telephone'];
        }
        
        $this->pdf->MultiCell(80, 5, implode("\n", $buyerInfo), 0, 'L');
        
        $this->currentY = $this->pdf->GetY();
    }

    private function addItemsTable($product, $sale) {
        // Table header
        $this->pdf->SetFont('helvetica', 'B', $this->fontSize);
        $this->pdf->SetFillColor(243, 244, 246);
        
        $this->pdf->SetY($this->currentY);
        $this->pdf->Cell(80, 10, 'Description', 1, 0, 'L', 1);
        $this->pdf->Cell(25, 10, 'Prix HT', 1, 0, 'R', 1);
        $this->pdf->Cell(15, 10, 'TVA %', 1, 0, 'C', 1);
        $this->pdf->Cell(25, 10, 'Prix TTC', 1, 0, 'R', 1);
        $this->pdf->Cell(15, 10, 'Qté', 1, 0, 'C', 1);
        $this->pdf->Cell(30, 10, 'Total TTC', 1, 1, 'R', 1);
        
        // Calculate values
        $prix_ht = $product['prix_ht'] ?? ($product['prix'] / (1 + (($product['taux_tva'] ?? 20) / 100)));
        $taux_tva = $product['taux_tva'] ?? 20;
        $prix_ttc = $product['prix'] ?? 0;
        $total_ht = $prix_ht;
        $total_ttc = $prix_ttc;
        $montant_tva = $total_ttc - $total_ht;
        
        // Get product description and prepare it for multi-line display
        $description = $product['description'] ?? 'Produit';
        $maxWidth = 80; // Max width for the description cell
        $lineHeight = 5; // Height of each line
        $maxLines = 3; // Maximum number of lines to show
        
        // Split the description into lines that fit the cell width
        $descriptionLines = $this->pdf->getStringHeight($maxWidth, $this->pdf->getFontSize(), $this->pdf->getFontFamily(), $this->pdf->getFontStyle(), $description);
        $descriptionLines = $this->pdf->getNumLines($description, $maxWidth);
        $cellHeight = min($descriptionLines, $maxLines) * $lineHeight;
        
        // Start the row
        $this->pdf->SetFont('helvetica', '', $this->fontSize);
        $startY = $this->pdf->GetY();
        
        // Draw description cell with multi-line text
        $this->pdf->MultiCell(80, $lineHeight, $description, 1, 'L', false, 0, $this->pdf->GetX(), $startY, true, 0, false, true, $maxLines * $lineHeight, 'T');
        
        // Draw other cells with the same height as the description cell
        $this->pdf->SetXY(80 + $this->margin, $startY);
        $this->pdf->Cell(25, $cellHeight, number_format($prix_ht, 2, ',', ' ') . ' €', 1, 0, 'R');
        $this->pdf->Cell(15, $cellHeight, number_format($taux_tva, 2, ',', ' '), 1, 0, 'C');
        $this->pdf->Cell(25, $cellHeight, number_format($prix_ttc, 2, ',', ' ') . ' €', 1, 0, 'R');
        $this->pdf->Cell(15, $cellHeight, '1', 1, 0, 'C');
        $this->pdf->Cell(30, $cellHeight, number_format($prix_ttc, 2, ',', ' ') . ' €', 1, 1, 'R');
        
        // Update current Y position based on the tallest cell
        $endY = $startY + $cellHeight;
        $this->pdf->SetY($endY);
        
        // Add tax summary
        $this->currentY = $this->pdf->GetY();
        $this->pdf->SetFont('helvetica', '', $this->fontSize);
        $this->pdf->SetXY(120, $this->currentY);
        $this->pdf->Cell(60, 10, 'Total HT:', 0, 0, 'R');
        $this->pdf->Cell(30, 10, number_format($total_ht, 2, ',', ' ') . ' €', 0, 1, 'R');
        
        $this->pdf->SetX(120);
        $this->pdf->Cell(60, 10, 'TVA ' . number_format($taux_tva, 2, ',', ' ') . '%:', 0, 0, 'R');
        $this->pdf->Cell(30, 10, number_format($montant_tva, 2, ',', ' ') . ' €', 0, 1, 'R');
        
        $this->pdf->SetFont('helvetica', 'B', $this->fontSize);
        $this->pdf->SetX(120);
        $this->pdf->Cell(60, 10, 'Total TTC:', 0, 0, 'R');
        $this->pdf->Cell(30, 10, number_format($total_ttc, 2, ',', ' ') . ' €', 0, 1, 'R');
        
        $this->currentY = $this->pdf->GetY();
    }

    private function addPaymentInfo($sale) {
        $this->pdf->SetFont('helvetica', '', $this->smallFontSize);
        $this->pdf->SetXY($this->margin, $this->currentY + 5);
        
        $terms = [
            'Conditions de paiement' => 'Paiement comptant',
            'Date d\'échéance' => 'À réception de la facture',
            'Moyens de paiement' => 'Carte bancaire, Virement',
            'IBAN' => 'FR76 XXXX XXXX XXXX XXXX XXXX XXXX',
            'BIC' => 'XXXXXXXXXXX',
            'TVA non applicable - art. 293 B du CGI'
        ];
        
        $text = '';
        foreach ($terms as $key => $value) {
            if (is_numeric($key)) {
                $text .= "• $value\n";
            } else {
                $text .= "• $key : $value\n";
            }
        }
        
        $this->pdf->MultiCell(0, 5, $text, 0, 'L');
        
        // Thank you note
        $this->pdf->SetY($this->pdf->GetY() + 10);
        $this->pdf->SetFont('helvetica', 'I', $this->fontSize);
        $this->pdf->Cell(0, 5, 'Merci pour votre confiance !', 0, 1, 'C');
    }
}
