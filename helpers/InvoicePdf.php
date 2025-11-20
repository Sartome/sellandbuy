<?php

class InvoicePdf {
    private function escapeText($text) {
        $text = str_replace("\\", "\\\\", $text);
        $text = str_replace("(", "\\(", $text);
        $text = str_replace(")", "\\)", $text);
        return $text;
    }

    public function outputInvoice(array $sale, array $product = null, array $buyer = null, array $seller = null) {
        $lines = [];
        $lines[] = 'Facture #' . ($sale['id'] ?? '');
        $lines[] = '';
        $lines[] = 'Date: ' . ($sale['created_at'] ?? '');
        $lines[] = '';
        if ($buyer) {
            $lines[] = 'Client:';
            $lines[] = ($buyer['prenom'] ?? '') . ' ' . ($buyer['nom'] ?? '');
            $lines[] = $buyer['email'] ?? '';
            if (!empty($buyer['adresse'])) {
                $lines[] = $buyer['adresse'];
            }
            $lines[] = '';
        }
        if ($seller) {
            $lines[] = 'Vendeur:';
            $sellerName = trim(($seller['prenom'] ?? '') . ' ' . ($seller['nom'] ?? ''));
            $lines[] = $sellerName;
            $lines[] = $seller['email'] ?? '';
            $lines[] = '';
        }
        if ($product) {
            $lines[] = 'Produit:';
            $lines[] = $product['description'] ?? '';
            $lines[] = 'Prix unitaire: ' . number_format((float)($product['prix'] ?? 0), 2, ',', ' ') . ' EUR';
            $lines[] = '';
        }
        $lines[] = 'Montant facture: ' . number_format((float)($sale['amount'] ?? 0), 2, ',', ' ') . ' EUR';
        $lines[] = '';
        $text = implode("\n", $lines);
        $this->outputSimplePdf('facture-' . ($sale['id'] ?? '0') . '.pdf', $text);
    }

    private function outputSimplePdf(string $filename, string $text) {
        // Nettoyer tout output précédent pour ne pas corrompre le PDF
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $eol = "\n";

        $content = "BT" . $eol . "/F1 12 Tf" . $eol . "50 800 Td" . $eol;
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $escaped = $this->escapeText($line);
            $content .= "(" . $escaped . ") Tj" . $eol . "0 -16 Td" . $eol;
        }
        $content .= "ET" . $eol;

        $len = strlen($content);

        $objects = [];
        $objects[] = "1 0 obj" . $eol . "<< /Type /Catalog /Pages 2 0 R >>" . $eol . "endobj" . $eol;
        $objects[] = "2 0 obj" . $eol . "<< /Type /Pages /Kids [3 0 R] /Count 1 >>" . $eol . "endobj" . $eol;
        $objects[] = "3 0 obj" . $eol . "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>" . $eol . "endobj" . $eol;
        $objects[] = "4 0 obj" . $eol . "<< /Length " . $len . " >>" . $eol . "stream" . $eol . $content . "endstream" . $eol . "endobj" . $eol;
        $objects[] = "5 0 obj" . $eol . "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>" . $eol . "endobj" . $eol;

        $pdf = "%PDF-1.4" . $eol;
        $offsets = [];
        foreach ($objects as $i => $obj) {
            $offsets[$i + 1] = strlen($pdf);
            $pdf .= $obj;
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref" . $eol . "0 " . (count($objects) + 1) . $eol;
        $pdf .= "0000000000 65535 f " . $eol;
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . $eol;
        }
        $pdf .= "trailer<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>" . $eol;
        $pdf .= "startxref" . $eol . $xrefPos . $eol . "%%EOF";

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }
}
