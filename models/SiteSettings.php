<?php
/**
 * Modèle pour la gestion des paramètres du site
 */

class SiteSettings {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Récupérer une valeur de paramètre
     */
    public function get($key, $default = null) {
        $stmt = $this->db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['setting_value'] : $default;
    }

    /**
     * Définir une valeur de paramètre
     */
    public function set($key, $value, $description = null) {
        $stmt = $this->db->prepare("
            INSERT INTO site_settings (setting_key, setting_value, description) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            setting_value = VALUES(setting_value),
            description = VALUES(description)
        ");
        return $stmt->execute([$key, $value, $description]);
    }

    /**
     * Récupérer tous les paramètres
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM site_settings ORDER BY setting_key");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer le taux de taxe
     */
    public function getTaxRate() {
        return (float)$this->get('tax_rate', 0);
    }

    /**
     * Vérifier si les taxes sont activées
     */
    public function isTaxEnabled() {
        return (bool)$this->get('tax_enabled', 0);
    }

    /**
     * Récupérer le nom de la taxe
     */
    public function getTaxName() {
        return $this->get('tax_name', 'TVA');
    }

    /**
     * Calculer le montant de la taxe
     */
    public function calculateTax($amount) {
        if (!$this->isTaxEnabled()) {
            return 0;
        }
        return $amount * ($this->getTaxRate() / 100);
    }

    /**
     * Calculer le prix TTC
     */
    public function calculatePriceWithTax($amount) {
        return $amount + $this->calculateTax($amount);
    }

    /**
     * Calculer le prix HT
     */
    public function calculatePriceWithoutTax($amount) {
        if (!$this->isTaxEnabled()) {
            return $amount;
        }
        $taxRate = $this->getTaxRate();
        return $amount / (1 + ($taxRate / 100));
    }

    /**
     * Ajouter une nouvelle taxe
     */
    public function addTax($name, $rate, $description = '') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO site_settings (setting_key, setting_value, description) 
                VALUES (?, ?, ?)
            ");
            $key = 'tax_' . time() . '_' . rand(1000, 9999);
            $value = json_encode([
                'name' => $name,
                'rate' => $rate,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $stmt->execute([$key, $value, $description]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Supprimer une taxe
     */
    public function deleteTax($taxId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM site_settings WHERE id = ?");
            return $stmt->execute([$taxId]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Obtenir toutes les taxes
     */
    public function getAllTaxes() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM site_settings 
                WHERE setting_key LIKE 'tax_%' 
                AND setting_key NOT IN ('tax_rate', 'tax_enabled', 'tax_name')
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            $taxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Décoder les données JSON
            foreach ($taxes as &$tax) {
                $taxData = json_decode($tax['setting_value'], true);
                if ($taxData) {
                    $tax['tax_name'] = $taxData['name'];
                    $tax['tax_rate'] = $taxData['rate'];
                    $tax['tax_description'] = $taxData['description'];
                    $tax['created_at'] = $taxData['created_at'];
                }
            }
            
            return $taxes;
        } catch (Exception $e) {
            return [];
        }
    }
}
